<?php

namespace App\Providers\LDAP;

use App\Providers\LDAP\Connection\Credentials;
use Exception;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Database\Eloquent\Model;

class LDAPEloquentUserProvider extends EloquentUserProvider
{
    /**
     * @var boolean $createUser
     */
    protected $createUser;
    /**
     * @var Application $app The application instance
     */
    private $app;
    /**
     * @var Log $logger Our logger instance
     */
    private $logger;
    /**
     * @var LDAPProvider $ldapProvider
     */
    private $ldapProvider;

    /**
     * Create a new LDAP & Eloquent user provider.
     *
     * @param Application $app
     * @param Log $logger
     * @param \Illuminate\Contracts\Hashing\Hasher $hasher
     */
    public function __construct(Application $app, Log $logger, Hasher $hasher)
    {
        parent::__construct($hasher, null);
        $this->app = $app;
        $this->logger = $logger;
        $this->createUser = false;
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable| Model | null
     */
    public function retrieveByCredentials(array $credentials)
    {
        try {
            // Get the user from the database if they exist
            $user = parent::retrieveByCredentials($credentials);

            if ($user) {
                return $user;
            }

            if ($this->createUser) {
                return $this->attemptToCreateUser($credentials);
            }

            return null;
        } catch (Exception $exception) {
            $this->logger->error("Unable to retrieve user", [
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    protected function attemptToCreateUser(array $credentials)
    {
        try {
            $cn = $this->getCnFromCredentials($credentials);
            $ldapUser = $this->ldapProvider->retrieveLDAPUserByCN($cn);

            if (!$ldapUser) {
                return null;
            }

            return $this->createAppUserFromLDAPUser($ldapUser);
        } catch (Exception $exception) {
            $this->logger->error("Unable to create user", [
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Convert a credentials set to a CN
     *
     * @param array $credentials The User's Credentials
     * @return string | null The User's CN or null if unable to calculate it
     */
    protected function getCnFromCredentials(array $credentials)
    {
        return isset($credentials['username']) ? $credentials['username'] : null;
    }

    //** Customise from here down **/

    /**
     * Create a user model from the LDAP user data
     *
     * @param LDAPUser $ldapUser The LDAPUser instance to turn into the configured user model
     * @return Model The newly created user model
     */
    protected function createAppUserFromLDAPUser(LDAPUser $ldapUser)
    {
        $user = $this->createModel();
        $this->updateAppUserFromLDAPUser($user, $ldapUser);

        return $user;
    }

    /**
     * Update the user model from the LDAPUser
     *
     * @param Model $user The user model to update, this is done by side-effect
     * @param LDAPUser $ldapUser The LDAP User to use as the source of information
     */
    protected function updateAppUserFromLDAPUser(Model $user, LDAPUser $ldapUser)
    {
        $user->username = $ldapUser->cn();
        $user->email = $ldapUser->email();
        $user->name = $ldapUser->givenName() . ' ' . $ldapUser->surname();

        $user->source = 'LDAP';
        $user->password = "";
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable | Model $user
     * @param  array $credentials
     * @return bool
     * @throws Exception
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        try {
            $cn = $this->getCnFromCredentials($credentials);
            $dn = $this->getDnFromCN($cn);
            $credentials = $this->app->make(Credentials::class, ['dn'=>$dn, 'password'=>$credentials['password']]);
            $success = $this->ldapProvider->canLogInToLDAP($credentials);
        } catch (Exception $exception) {
            $this->logger->error("Unable to authenticate", [
                'error' => $exception->getMessage(),
            ]);

            return false;
        }

        // Saving the user here so we only add them after they have passed authentication from LDAP
        if ($success && $this->createUser && !$user->exists) {
            $success = $user->save();
        }

        return $success;
    }

    /**
     * Convert a CN to a DN
     *
     * @param string $cn The User's CN
     * @return string | null The User's DN or null if unable to calculate it
     */
    protected function getDnFromCN($cn)
    {
        $ldapUser = $this->ldapProvider->retrieveLDAPUserByCN($cn);
        if( $ldapUser===null ) {
            return null;
        }
        return $ldapUser->dn();
    }

    /**
     * @param LDAPProvider $ldapProvider
     * @return LDAPEloquentUserProvider
     */
    public function setLdapProvider($ldapProvider)
    {
        $this->ldapProvider = $ldapProvider;
        return $this;
    }

    /**
     * @param $createUser boolean
     * @return LDAPEloquentUserProvider
     */
    public function setCreateUser($createUser)
    {
        $this->createUser = $createUser;
        return $this;
    }
}


<?php


namespace App\Providers\LDAP;

use Exception;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Contracts\Foundation\Application;
use App\Providers\LDAP\Connection\CredentialsInterface;


class LDAPProvider
{
    /**
     * @var Log
     */
    private $logger;

    /**
     * @var string $endpoint The LDAP endpoint URI
     */
    private $endpoint;

    /**
     * @var resource $ldapConnection LDAP connection
     */
    private $ldapConnection;

    /**
     * @var
     */
    private $baseDN;

    /**
     * @var CredentialsInterface
     */
    private $lookupUser;
    /**
     * @var Application
     */
    private $app;


    /**
     * LDAPProvider constructor.
     * @param Log $logger
     * @param Application $app
     */
    public function __construct(Log $logger, Application $app)
    {
        $this->logger = $logger;
        $this->app = $app;
    }


    /**
     * @return void
     */
    function __destruct()
    {
        $this->unbindFromLDAP();
    }


    /**
     * Ensure we have a connection to the LDAP server
     *
     * @return bool If we have a connection (note this may not be accurate with OpenLDAP 2 - see the ldap_connect documentation)
     */
    private function ensureLDAPConnection()
    {
        if ($this->ldapConnection) {
            return true;
        }

        if (!extension_loaded('ldap')) {
            $this->logger->error("LDAP extension not installed");
            return false;
        }

        try {
            $this->ldapConnection = ldap_connect($this->endpoint);
        } catch (Exception $exception) {
            $this->logger->error("Unable to connect to LDAP endpoint", [
                'error' => $exception->getMessage()
            ]);

            return false;
        }

        if (!$this->ldapConnection) {
            $this->logger->error("Unable to connect to LDAP endpoint", [
                'error' => ldap_error($this->ldapConnection)
            ]);

            return false;
        }

        ldap_set_option($this->ldapConnection, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->ldapConnection, LDAP_OPT_REFERRALS, 0);
        return true;
    }


    /**
     * Fetch information about a user from LDAP and return an LDAP user object
     *
     * @param string $cn The CN of the user to lookup
     * @return LDAPUser | null The LDAP user data or null if the user doesn't exist
     */
    public function retrieveLDAPUserByCN($cn)
    {
        if (!isset($cn)) {
            return null;
        }

        if (!$this->bindToLDAP($this->lookupUser)) {
            return null;
        }

        try {
            $result = ldap_search($this->ldapConnection, $this->baseDN, 'cn=' . $cn);
        } catch (Exception $exception) {
            $this->logger->error("Unable to search LDAP", [
                'error' => $exception->getMessage()
            ]);

            return null;
        }

        if (!$result) {
            $this->logger->error("Unable to search LDAP", [
                'error' => ldap_error($this->ldapConnection)
            ]);

            return null;
        }

        if (ldap_count_entries($this->ldapConnection, $result) == 0) {
            $this->logger->error("Zero results returned from LDAP");
            return null;
        }

        if (ldap_count_entries($this->ldapConnection, $result) > 1) {
            $this->logger->error("More than one result returned from LDAP");
            return null;
        }
	
        $entries = ldap_get_entries($this->ldapConnection, $result);
        $user = $this->app->make(LDAPUser::class, ["data"=>$entries]);
        return $user;
    }

    public function canLogInToLDAP(CredentialsInterface $credentials) {
        try {
            return $this->bindToLDAP($credentials);
        } catch (Exception $exception) {
            $this->logger->error("Unable to attempt login", [
                'error' => $exception->getMessage()
            ]);

            return false;
        }
    }

    //** LDAP bindings **//

    /**
     * Attempt a bind to ldap with optional credentials
     *
     * @param CredentialsInterface $credentials
     * @return bool If the bind was successful
     * @internal param null|string $dn The optional DN to attempt the bind with
     * @internal param null|string $password The optional password to attempt the bind with
     */
    private function bindToLDAP(CredentialsInterface $credentials)
    {
        if (!$this->ensureLDAPConnection()) {
            return false;
        }

        try {
            $ldapBoundOK = ldap_bind($this->ldapConnection, $credentials->dn(), $credentials->password());
        } catch (Exception $exception) {
            $this->logger->error("Unable to bind to endpoint", [
                'error' => $exception->getMessage()
            ]);

            return false;
        }

        if (!$ldapBoundOK) {
            $this->logger->error('Unable to bind to endpoint', [
                'error' => ldap_error($this->ldapConnection)
            ]);

            return false;
        }

        return true;
    }

    /**
     * Unbind from LDAP
     * @return void
     */
    public function unbindFromLDAP()
    {
        if (!$this->ldapConnection) {
            return;
        }

        try {
            ldap_unbind($this->ldapConnection);
        } catch (Exception $exception) {
            $this->logger->error("Unable to do unbind from endpoint", [
                'error' => $exception->getMessage()
            ]);
        }

        $this->ldapConnection = null;
    }

    /**
     * @param string $endpoint
     * @return LDAPProvider
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    /**
     * @param CredentialsInterface $lookupUser
     * @return LDAPProvider
     */
    public function setLookupUser($lookupUser)
    {
        $this->lookupUser = $lookupUser;
        return $this;
    }

    /**
     * @param mixed $baseDN
     * @return LDAPProvider
     */
    public function setBaseDN($baseDN)
    {
        $this->baseDN = $baseDN;
        return $this;
    }
}

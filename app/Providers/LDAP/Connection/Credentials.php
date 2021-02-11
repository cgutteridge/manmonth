<?php


namespace App\Providers\LDAP\Connection;


class Credentials implements CredentialsInterface
{
    private $dn;
    private $password;

    /**
     * LDAPCredentials constructor.
     * @param string $dn
     * @param string $password
     */
    public function __construct($dn, $password)
    {
        $this->dn = $dn;
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function password()
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function dn()
    {
        return $this->dn;
    }
}
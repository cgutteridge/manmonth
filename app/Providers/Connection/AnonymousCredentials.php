<?php


namespace App\Providers\LDAP\Connection;


class AnonymousCredentials implements CredentialsInterface
{

    /**
     * @return string
     */
    public function password()
    {
        return null;
    }

    /**
     * @return string
     */
    public function dn()
    {
        return null;
    }
}
<?php

namespace App\Providers\LDAP\Connection;

interface CredentialsInterface
{
    /**
     * @return string
     */
    public function password();

    /**
     * @return string
     */
    public function dn();
}
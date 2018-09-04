<?php

namespace App\Providers\LDAP;

class LDAPUser
{
    private $data = null;

    /**
     * LDAPData constructor.
     * @param $data
     */
    public function __construct($data)
    {
        if (isset($data[0])) {
            $this->data = $data[0];
        }
    }

    public function getKey($key, $index = null)
    {
        if ($index !== null) {
            return is_array($this->data[$key]) && isset($this->data[$key][$index]) ? $this->data[$key][$index] : null;
        }

        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    public function isValid()
    {
        return $this->data != null;
    }

    public function cn()
    {
        return $this->getKey('cn', 0);
    }

    public function givenName()
    {
        return $this->getKey('givenname', 0);
    }

    public function surname()
    {
        return $this->getKey('sn', 0);
    }

    public function email()
    {
        return $this->getKey('mail', 0);
    }

    public function dn()
    {
        return $this->getKey('dn');
    }
}

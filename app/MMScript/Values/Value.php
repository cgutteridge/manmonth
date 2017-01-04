<?php
/**
 * Created by PhpStorm.
 * User: cjg
 * Date: 09/09/2016
 * Time: 18:55
 */

namespace App\MMScript\Values;

abstract class Value
{

    public $value;

    /**
     * AbstractValue constructor.
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return StringValue
     */
    public function castString()
    {
        return new StringValue((string)($this->value));
    }
}
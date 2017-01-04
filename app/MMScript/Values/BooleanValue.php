<?php
/**
 * Created by PhpStorm.
 * User: cjg
 * Date: 09/09/2016
 * Time: 18:58
 */

namespace App\MMScript\Values;

class BooleanValue extends Value
{
    /**
     * @return StringValue
     */
    public function castString()
    {
        if ($this->value) {
            return new StringValue("TRUE");
        } else {
            return new StringValue("FALSE");
        }
    }
}
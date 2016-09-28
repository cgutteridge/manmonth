<?php

namespace App\Fields;

use App\MMScript\Values\BooleanValue;
use App\MMScript\Values\NullValue;

class BooleanField extends Field
{
    /**
     * @return string
     */
    public function valueValidationCode()
    {
        return parent::valueValidationCode() . "|boolean";
    }

    /**
     * @return array
     */
    public function fieldValidationArray()
    {
        return array_merge(parent::fieldValidationArray(), [
            'type' => 'required|in:boolean',
            'default' => 'boolean',
        ]);
    }

    /**
     * @param boolean $value
     * @return BooleanValue|NullValue
     */
    public function makeValue($value)
    {
        if (!isset($value)) {
            if (isset($this->data["default"])) {
                return new BooleanValue($this->data["default"]);
            }
            return new NullValue();
        }
        return new BooleanValue($value);
    }

}


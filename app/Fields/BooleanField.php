<?php

namespace App\Fields;

use App\MMScript\Values\BooleanValue;
use App\MMScript\Values\NullValue;

class BooleanField extends Field
{
    /**
     * @return array
     */
    protected function valueValidationCodeParts()
    {
        $parts = parent::valueValidationCodeParts();
        $parts["boolean"] = true;
        return $parts;
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


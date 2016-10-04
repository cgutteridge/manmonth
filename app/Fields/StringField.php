<?php

namespace App\Fields;

use App\MMScript\Values\StringValue;
use App\MMScript\Values\NullValue;

class StringField extends Field
{

    /**
     * @return array
     */
    protected function valueValidationCodeParts()
    {
        $parts = parent::valueValidationCodeParts();
        $parts["string"] = true;
        return $parts;
    }

    /**
     * @return array
     */
    public function fieldValidationArray()
    {
        return array_merge(parent::fieldValidationArray(), [
            'type' => 'required|in:string',
            'default' => 'string',
        ]);
    }

    /**
     * @param string $value
     * @return NullValue|StringValue
     */
    public function makeValue($value)
    {
        if (!isset($value)) {
            if (isset($this->data["default"])) {
                return new StringValue($this->data["default"]);
            }
            return new NullValue();
        }
        return new StringValue($value);
    }
}


<?php

namespace App\Fields;

use App\MMScript\Values\IntegerValue;
use App\MMScript\Values\NullValue;

class IntegerField extends Field
{
    /**
     * @return array
     */
    protected function valueValidationCodeParts()
    {
        $parts = parent::valueValidationCodeParts();
        $parts["integer"] = true;
        if (isset($this->data["min"])) {
            $parts["min:" . $this->data["min"]] = true;
        }
        if (isset($this->data["max"])) {
            $parts["max:" . $this->data["max"]] = true;
        }
        return $parts;
    }


    /**
     * @return array
     */
    public function fieldValidationArray()
    {
        return array_merge(parent::fieldValidationArray(), [
            'type' => 'required|in:integer',
            'default' => 'integer',
        ]);
    }

    /**
     * @param int $value
     * @return IntegerValue|NullValue
     */
    public function makeValue($value)
    {
        if (!isset($value)) {
            if (isset($this->data["default"])) {
                return new IntegerValue($this->data["default"]);
            }
            return new NullValue();
        }
        return new IntegerValue($value);
    }

    /**
     * Gives a list of field descriptions for the properties of this field.
     */
    protected function metaFieldDefinitions()
    {
        return array_merge(
            parent::metaFieldDefinitions(),
            [
                [
                    "name" => "min",
                    "label" => "Minimum value",
                    "type" => "integer"
                ],
                [
                    "name" => "max",
                    "label" => "Maximum value",
                    "type" => "integer"
                ]
            ]
        );
    }
}


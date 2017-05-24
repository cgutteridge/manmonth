<?php

namespace App\Fields;

use App\MMScript\Values\DecimalValue;
use App\MMScript\Values\NullValue;

class DecimalField extends Field
{
    /**
     * @return array
     */
    protected function valueValidationCodeParts()
    {
        $parts = parent::valueValidationCodeParts();
        $parts["numeric"] = true;
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
            'type' => 'required|in:decimal',
            'default' => 'numeric',
        ]);
    }

    /**
     * @param float $value
     * @return DecimalValue|NullValue
     */
    public function makeValue($value)
    {
        if (!isset($value)) {
            if (isset($this->data["default"])) {
                return new DecimalValue($this->data["default"]);
            }
            return new NullValue();
        }
        return new DecimalValue($value);
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
                    "type" => "decimal"
                ],
                [
                    "name" => "max",
                    "label" => "Maximum value",
                    "type" => "decimal"
                ]
            ]
        );
    }
}


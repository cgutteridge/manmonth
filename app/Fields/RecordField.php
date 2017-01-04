<?php

namespace App\Fields;

use App\MMScript\Values\NullValue;
use App\MMScript\Values\RecordValue;
use App\Models\Record;

class RecordField extends Field
{

    /**
     * @return array
     */
    public function fieldValidationArray()
    {
        return array_merge(parent::fieldValidationArray(), [
            'type' => 'required|in:record'
        ]);
    }

    /**
     * @param Record $value
     * @return RecordValue|NullValue
     */
    public function makeValue($value)
    {
        if (!isset($value)) {
            return new NullValue();
        }
        return new RecordValue($value);
    }
}


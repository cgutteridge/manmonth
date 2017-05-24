<?php

namespace App\Fields;

use App\MMScript\Values\StringValue;
use App\MMScript\Values\NullValue;

class LongTextField extends StringField
{

    /**
     * @return array
     */
    public function fieldValidationArray()
    {
        return array_merge(parent::fieldValidationArray(), [
            'type' => 'required|in:longtext',
            'default' => 'string',
        ]);
    }

}


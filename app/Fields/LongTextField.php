<?php

namespace App\Fields;

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


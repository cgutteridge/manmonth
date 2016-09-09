<?php

namespace App\Fields;

class StringField extends Field
{
    // return the laravel validate code to validate a value for this field
    public function valueValidationCode() {
        return parent::valueValidationCode()."|string";
    }

    // return the laravel validate code array to validate this field type
    public function fieldValidationArray() {
        return array_merge( parent::fieldValidationArray(), [
          'type' => 'required|in:string',
          'default' => 'string',
        ]);
    }


}


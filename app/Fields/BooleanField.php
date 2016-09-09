<?php

namespace App\Fields;

class BooleanField extends Field
{
    // return the laravel validate code to validate a value for this field
    public function valueValidationCode() {
        return parent::valueValidationCode()."|boolean";
    }

    // return the laravel validate code array to validate this field type
    public function fieldValidationArray() {
        return array_merge( parent::fieldValidationArray(), [
          'type' => 'required|in:boolean',
          'default' => 'boolean',
        ]);
    }


}


<?php

namespace App\Fields;

use App\MMScript\Values\StringValue;
use App\MMScript\Values\NullValue;

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

    public function makeValue( $value ) {
        if( !isset( $value )) {
            if( isset( $this->data["default"])) {
                return new StringValue( $this->data["default"] );
            }
            return new NullValue();
        }
        return new StringValue( $value );
    }
}


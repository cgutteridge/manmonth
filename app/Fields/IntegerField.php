<?php

namespace App\Fields;

use App\MMScript\Values\IntegerValue;
use App\MMScript\Values\NullValue;

class IntegerField extends Field
{
    // return the laravel validate code to validate a value for this field
    public function valueValidationCode() {
        $code = parent::valueValidationCode();
        $code.= "|integer";
        if( isset($this->data["min"]) ) { $code .= "|min:".$this->data["min"]; }
        if( isset($this->data["max"]) ) { $code .= "|max:".$this->data["max"]; }
        return $code;
    }

    // return the laravel validate code array to validate this field type
    public function fieldValidationArray() {
        return array_merge( parent::fieldValidationArray(), [
          'type' => 'required|in:integer',
          'default' => 'integer',
        ]);
    }

    public function makeValue( $value ) {
        if (!isset($value)) {
            if( isset( $this->data["default"])) {
            return new IntegerValue( $this->data["default"] );
          }
          return new NullValue();
        }
        return new IntegerValue( $value );
    }
}


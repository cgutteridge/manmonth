<?php

namespace App\Fields;

use App\MMScript\Values\IntegerValue;
use App\MMScript\Values\NullValue;

class IntegerField extends Field
{
    /**
     * @return string
     */
    public function valueValidationCode() {
        $code = parent::valueValidationCode();
        $code.= "|integer";
        if( isset($this->data["min"]) ) { $code .= "|min:".$this->data["min"]; }
        if( isset($this->data["max"]) ) { $code .= "|max:".$this->data["max"]; }
        return $code;
    }

    /**
     * @return array
     */
    public function fieldValidationArray() {
        return array_merge( parent::fieldValidationArray(), [
          'type' => 'required|in:integer',
          'default' => 'integer',
        ]);
    }

    /**
     * @param int $value
     * @return IntegerValue|NullValue
     */
    public function makeValue($value ) {
        if (!isset($value)) {
            if( isset( $this->data["default"])) {
            return new IntegerValue( $this->data["default"] );
          }
          return new NullValue();
        }
        return new IntegerValue( $value );
    }
}


<?php

namespace App\Fields;

class DecimalField extends Field
{
    // return the laravel validate code to validate a value for this field
    public function valueValidationCode() {
        $code = parent::valueValidationCode();
        $code.= "|numeric";
        if( isset($this->data["min"]) ) { $code .= "|min:".$this->data["min"]; }
        if( isset($this->data["max"]) ) { $code .= "|max:".$this->data["max"]; }
        return $code;
    }

    // return the laravel validate code array to validate this field type
    public function fieldValidationArray() {
        return array_merge( parent::fieldValidationArray(), [
          'type' => 'required|in:decimal',
          'default' => 'numeric',
        ]);
    }


}


<?php

namespace App\Exceptions;

use Exception;

class ValidationException extends Exception
{
    protected $classname;
    public $field;
    public $value;
    public $errors;

    public function __construct( $classname, $field, $value, $errors ) {
        $this->classname = $classname; 
        $this->field = $field; 
        $this->value = $value; 
        $this->errors = $errors; 
     
        $message = "Validation error in $classname.$field value=".json_encode( $this->value ).", errors=".json_encode( $this->errors );
        $code = null;         
        $previous = null;         
 
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

    // custom string representation of object
    public function __toString() {
        return __CLASS__ . ": ".$this->message;
    }

}


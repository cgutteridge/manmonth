<?php

namespace App\Exceptions;

use Exception;

class ParseException extends ScriptException
{
    public $message;
    public $script;
    public $offset;

    /**
     * ParseException constructor.
     * @param string $message
     * @param string $script
     * @param integer $offset the character offset of the error in script.
     */
    public function __construct($message, $script, $offset ) {
        $this->message = $message; 
        $this->script = $script; 
        $this->offset = $offset; 
     
        $message = "$message near: ";
	    $message .= substr( $script,0,$offset );
	    $message .= "<HERE>";
    	$message .= substr( $script,$offset );

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


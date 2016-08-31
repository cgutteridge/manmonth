<?php

namespace App;

use Exception;

class Field
{
    public $data;

    public function __construct( $data ) {
        $this->data = $data; 
    }

    public function validationCode() {
        $parts = [];
        if( @$this->data["required"] ) { $parts []= "required"; }
        if( $this->data["type"]=='string' ) { $parts []= "string"; }
        if( $this->data["type"]=='integer' ) { $parts []= "integer"; }
        if( $this->data["type"]=='decimal' ) { $parts []= "numeric"; }
        if( $this->data["type"]=='boolean' ) { $parts []= "boolean"; }
        if( @$this->data["min"] ) { $parts []= "min:".$this->data["min"]; }
        if( @$this->data["max"] ) { $parts []= "max:".$this->data["max"]; }
        return join( "|", $parts );
    }
}


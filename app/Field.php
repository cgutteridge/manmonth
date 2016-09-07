<?php

namespace App;

use Exception;
use Validator;

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

    public function required() { 
        return( true == @$this->data["required"] );
    }

    public static function validateData( $fieldData ) {
        if( $fieldData["type"] == "boolean" ) {
            self::validateBooleanField( $fieldData );
        } 
        if( $fieldData["type"] == "integer" ) {
            self::validateIntegerField( $fieldData );
        } 
        if( $fieldData["type"] == "decimal" ) {
            self::validateDecimalField( $fieldData );
        } 
        if( $fieldData["type"] == "string" ) {
            self::validateStringField( $fieldData );
        } 
        else {
            new Exception( "Code should not have reached this point .. field of type '".$fieldData["type"]."'" );
        } 
    }

    public static function validateBooleanField($data) {

        $validator = Validator::make(
          $data,
          [ 'name' => 'required|alpha_dash|min:2|max:255', 
            'type' => 'required|in:boolean',
            'required' => 'boolean',
            'default' => 'boolean' ]);

        if($validator->fails()) {
            throw new ValidationException( "RecordType", "data field (boolean)", $data, $validator->errors() );
        }
    }

    public static function validateStringField($data) {

        $validator = Validator::make(
          $data,
          [ 'name' => 'required|alpha_dash|min:2|max:255', 
            'type' => 'required|in:string',
            'required' => 'boolean',
            'default' => 'string' ]);

        if($validator->fails()) {
            throw new ValidationException( "RecordType", "data field (string)", $data, $validator->errors() );
        }
    }

    public static function validateIntegerField($data) {

        $validator = Validator::make(
          $data,
          [ 'name' => 'required|alpha_dash|min:2|max:255', 
            'type' => 'required|in:integer',
            'required' => 'boolean',
            'min' => 'integer', 
            'max' => 'integer', 
            'default' => 'integer' ]);

        if($validator->fails()) {
            throw new ValidationException( "RecordType", "data field (integer)", $data, $validator->errors() );
        }
    }

    public static function validateDecimalField($data) {

        $validator = Validator::make(
          $data,
          [ 'name' => 'required|alpha_dash|min:2|max:255', 
            'type' => 'required|in:decimal',
            'required' => 'boolean',
            'min' => 'numeric', 
            'max' => 'numeric', 
            'default' => 'numeric' ]);

        if($validator->fails()) {
            throw new ValidationException( "RecordType", "data field (decimal)", $data, $validator->errors() );
        }
    }



}


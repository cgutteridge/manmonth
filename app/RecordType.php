<?php

namespace App;

use Exception;
use Validator;

class RecordType extends DocumentPart
{
    public function records()
    {
        return $this->documentRevision->records()->where( "record_type_sid", $this->sid );
    }

    public function newRecord($data=array())
    {
        $this->validateRecordData( $data );

        $record = new Record();
        $record->documentRevision()->associate( $this->documentRevision );
        $record->record_type_sid = $this->sid;
        $record->data = json_encode( $data );
        $record->save();
        return $record;
    }

    // candidate for a trait or something?
    var $dataCache;
    public function data() {
        if( !$this->dataCache ) { 
            $this->dataCache = json_decode( $this->data, true );
        }  
        return $this->dataCache;
    }

    var $fieldsCache;
    public function fields() {
        if( !$this->fieldsCache ) { 
            $this->fieldsCache = [];
            foreach( $this->data()["fields"] as $fieldData ) {
                $this->fieldsCache []= new Field( $fieldData );
            }
        }  
        return $this->fieldsCache;
    }

    public function validateRecordData( $data ) {
        $validationCodes = [];
        foreach( $this->fields() as $field ) {
            $validationCodes[$field->data["name"]] = $field->validationCode();
        }

        $validator = Validator::make( $data, $validationCodes );

        if($validator->fails()) {
            throw new ValidationException( "Record", "data", $data, $validator->errors() );
        }
    }

    public static function validateName($name) {

        $validator = Validator::make(
        [ 'name' => $name ],
        [ 'name' => 'required|alpha_dash|min:2|max:255' ]);

        if($validator->fails()) {
            throw new ValidationException( "RecordType", "name", $name, $validator->errors() );
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

    public static function validateData($data) {

        $validator = Validator::make(
          $data,
          [ 'fields' => 'required|array', 
            'fields.*.type' => 'required|in:boolean,integer,decimal,string' ]);

        if($validator->fails()) {
            throw new ValidationException( "RecordType", "data", $data, $validator->errors() );
        }

        foreach( $data["fields"] as $field ) {
            if( $field["type"] == "boolean" ) {
                self::validateBooleanField( $field );
            } 
            if( $field["type"] == "integer" ) {
                self::validateIntegerField( $field );
            } 
            if( $field["type"] == "decimal" ) {
                self::validateDecimalField( $field );
            } 
            if( $field["type"] == "string" ) {
                self::validateStringField( $field );
            } 
            else {
                new Exception( "Code should not have reached this point" );
            } 
        }

    }

}



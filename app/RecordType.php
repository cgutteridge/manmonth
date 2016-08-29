<?php

namespace App;

use Exception;

class RecordType extends DocumentPart
{
    public function newRecord($data=array())
    {
// TODO validate inputs
        $record = new Record();
        $record->documentRevision()->associate( $this->documentRevision );
        $record->record_type_sid = $this->sid;
        $record->data = json_encode( $data );
        $record->save();
        return $record;
    }

    // monad
    public static $validators;
    public static function validators() {
        if( isset( self::$validators ) ) { return self::$validators; }

        self::$validators = array();
/*
        self::$validators["name"] = new \Structure\StringS();
        self::$validators["name"]->setLength(2);

        // currently the only field is, er, fields, but this structure leaves room to grow
        self::$validators["data"] = new \Structure\ArrayS();
        self::$validators["data"]->setFormat( array(
            "fields"=>"array",
        ));
        self::$validators["data"]->setCountStrict(true); // don't allow stray terms

        // this just checks the type is valid, ignores other properties
        self::$validators["field"] = new \Structure\ArrayS();
        self::$validators["field"]->setFormat( array(
            "type"=>"string{string,integer,decimal,boolean}",
        ));
       
        ////// the following are for each type of field 

        // string field
        self::$validators['string'] = new \Structure\ArrayS();
        self::$validators['string']->setFormat( array(
            "name"=>"string",
            "type"=>"string{string}",
            "default"=>"null|string", 
        ));
        self::$validators['string']->setCountStrict(true); // don't allow stray terms

        // integer field
        self::$validators['integer'] = new \Structure\ArrayS();
        self::$validators['integer']->setFormat( array(
            "name"=>"string",
            "type"=>"string{integer}",
            "default"=>"null|integer", 
            "min"=>"null|integer", 
            "max"=>"null|integer", 
        ));
        self::$validators['integer']->setCountStrict(true); // don't allow stray terms

        // decimal field
        self::$validators['decimal'] = new \Structure\ArrayS();
        self::$validators['decimal']->setFormat( array(
            "name"=>"string",
            "type"=>"string{decimal}",
            "default"=>"null|float", 
            "min"=>"null|float", 
            "max"=>"null|float", 
        ));
        self::$validators['decimal']->setCountStrict(true); // don't allow stray terms
        
        // boolean field
        self::$validators['boolean'] = new \Structure\ArrayS();
        self::$validators['boolean']->setFormat( array(
            "name"=>"string",
            "type"=>"string{boolean}",
            "default"=>"null|boolean", 
        ));
        self::$validators['boolean']->setCountStrict(true); // don't allow stray terms
*/
        return self::$validators; 
    }

    public static function validateName($name) {
        $validators = self::validators();
/*
        if( ! $validators["name"]->check( $name, $fail ) ) {
            throw new Exception( "Error ".json_encode( $fail )." in name in RecordType: ".json_encode( $name ) );
        }
*/
    }

    public static function validateData($data) {
        $validators = self::validators();
/*
        if( ! $validators["data"]->check( $data, $fail ) ) {
            throw new Exception( "Error ".json_encode( $fail )." in data in RecordType: ".json_encode( $data ) );
        }
        foreach( $data["fields"] as $field ) {
            if( ! $validators["field"]->check( $field, $fail ) ) {
                throw new Exception( "Error ".json_encode( $fail )." in a field in RecordType: ".json_encode( $field ) );
            }
            // bit more tricky, check that it's appropriate to the type
            if( ! $validators[$field['type']]->check( $field, $fail ) ) {
                throw new Exception( "Error ".json_encode( $fail )." in a '".$field['type']."' field in RecordType: ".json_encode( $field ) );
            }
            
        }
*/
    }

}



<?php

namespace App\Fields;

use Exception;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\DataStructValidationException;

abstract class Field
{
    // need to make this non static? Maybe by making a fieldFactory singleton
    public static function createFromData( $fieldData ) {
        if( $fieldData["type"]=="string" ) {
            return new StringField( $fieldData );
        } elseif( $fieldData["type"]=="decimal" ) {
            return new DecimalField( $fieldData );
        } elseif( $fieldData["type"]=="integer" ) {
            return new IntegerField( $fieldData );
        } elseif( $fieldData["type"]=="boolean" ) {
            return new BooleanField( $fieldData );
        } else {
            throw new Exception( "Unknown field type: '".$fieldData["type"]."'" );
        }
    }


    public $data;

    // this isn't written to the db so don't bother making data a json_encoded
    // param... but this is inconsistant with DocumentPart models.
    public function __construct( $data ) {
        $this->data = $data; 
    }

    // return the laravel validate code to validate a value for this field
    public function valueValidationCode() {
        $parts = [];
        if( @$this->data["required"] ) { $parts []= "required"; }

        if( $this->data["type"]=='string' ) { $parts []= "string"; }
        if( $this->data["type"]=='integer' ) { $parts []= "integer"; }
        if( $this->data["type"]=='decimal' ) { $parts []= "numeric"; }
        if( $this->data["type"]=='boolean' ) { $parts []= "boolean"; }

        return join( "|", $parts );
    }

    public function required() { 
        return( true == @$this->data["required"] );
    }

    // return the laravel validate code array to validate this field type
    public function fieldValidationArray() {
        return [ 
          'name' => 'required|alpha_dash|min:2|max:255', 
          'required' => 'boolean',
        ];
    }

    public function validate() {
        $validator = Validator::make( $this->data, $this->fieldValidationArray() );
        if($validator->fails()) {
//throw new \Exception("z0FISH");
            throw new DataStructValidationException( "RecordType", "data field", $this->data, $validator->errors() );
        }
    }

}


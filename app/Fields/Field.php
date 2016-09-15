<?php

namespace App\Fields;

use App\MMScript\Values\Value;
use Exception;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\DataStructValidationException;

abstract class Field
{
    // need to make this non static? Maybe by making a fieldFactory singleton
    /**
     * @param array $fieldData
     * @return Field
     * @throws Exception
     */
    public static function createFromData($fieldData ) {
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
    /**
     * Field constructor.
     * @param $data
     */
    public function __construct($data) {
        $this->data = $data; 
    }

    /**
     * Return the laravel validate code to validate a value for this field
     * Subclassed by non abstract versions of Field
     * @return string
     */
    public function valueValidationCode() {
        $parts = [];
        if( @$this->data["required"] ) { $parts []= "required"; }
        return join( "|", $parts );
    }

    /**
     * Is this a required field?
     * @return bool
     */
    public function required() {
        return( true == @$this->data["required"] );
    }

    /**
     * Return the human readable title for this field. Failing that, the
     * name string.
     * @return string
     */
    public function title() {
        if( @$this->data["title"] ) {
            return $this->data["title"];
        }
        return $this->data["name"];
    }

    /**
     * Give the description text for the field, or null if there is none.
     * @return string|null
     */
    public function description() {
        if( @$this->data["description"] ) {
            return $this->data["description"];
        }
        return null;
    }

    /**
     * Return the laravel validate array to validate data for this field
     * @return array
     */
    public function fieldValidationArray() {
        return [ 
          'name' => 'required|alpha_dash|min:2|max:255', 
          'required' => 'boolean',
        ];
    }

    /**
     * Check this field is valid
     * @throws DataStructValidationException
     */
    public function validate() {
        $validator = Validator::make( $this->data, $this->fieldValidationArray() );
        if($validator->fails()) {
            throw new DataStructValidationException( "Validation fail in field: ".join( ", ", $validator->errors() ));
        }
    }

    /**
     * Makes a MMScript value of this field type.
     * @param $value
     * @return Value
     */
    public abstract function makeValue($value );

}


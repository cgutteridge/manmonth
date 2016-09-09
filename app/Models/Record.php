<?php

namespace App\Models;

use App\Exceptions\ValidationException;
use Validator;


class Record extends DocumentPart
{
    public function recordType()
    {
        return $this->hasOne( 'App\Models\RecordType', 'sid', 'record_type_sid' )->where( 'document_revision_id', $this->document_revision_id );
    }

    public function forwardLinks()
    {
        return $this->hasMany( 'App\Models\Link', 'subject_sid', 'sid' )->where( 'document_revision_id', $this->document_revision_id );
    }
    public function backLinks()
    {
        return $this->hasMany( 'App\Models\Link', 'object_sid', 'sid' )->where( 'document_revision_id', $this->document_revision_id );
    }

    // candidate for a trait or something?
    var $dataCache;
    public function data() {
        if( !$this->dataCache ) { 
            $this->dataCache = json_decode( $this->data, true );
        }  
        return $this->dataCache;
    }

    // return a text representation and all associated records 
    // following subject->object direction links only.
    // does not (yet) worry about loops.
    function dumpText($indent="") {
        $r = "";
        $r.= $indent."".$this->recordType->name."#".$this->sid." ".$this->data."\n";
        foreach( $this->forwardLinks as $link ) {
             $r.=$indent."  ->".$link->linkType->name."->\n";
             $r.=$link->objectRecord->dumpText( $indent."    " );
        }
        return $r;
    }

    public function validateData() {
        $validationCodes = [];
        foreach( $this->recordType->fields() as $field ) {
            $validationCodes[$field->data["name"]] = $field->valueValidationCode();
        }

        $validator = Validator::make( $this->data(), $validationCodes );

        if($validator->fails()) {
            throw new DataStructValidationException( "Record", "data", $this->data(), $validator->errors() );
        }
    }

}



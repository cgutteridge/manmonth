<?php

namespace App;

use Exception;
use Validator;

class LinkType extends DocumentPart
{
    // candidate for a trait or something?
    var $dataCache;
    public function data() {
        if( !$this->dataCache ) { 
            $this->dataCache = json_decode( $this->data, true );
        }  
        return $this->dataCache;
    }

    public static function validateName($name) {

        $validator = Validator::make(
        [ 'name' => $name ],
        [ 'name' => 'required|alpha_dash|min:2|max:255' ]);

        if($validator->fails()) {
            throw new ValidationException( "LinkType", "name", $name, $validator->errors() );
        }
    }

    public static function validateData($data) {

        $validator = Validator::make(
          $data,
          [ 'domain_min' => 'min:0,integer', 
            'domain_max' => 'min:1,integer',
            'range_min' => 'min:0,integer',
            'range_max' => 'min:1,integer' ] );

        if($validator->fails()) {
            throw new ValidationException( "LinkType", "data", $data, $validator->errors() );
        }

        if( $data["domain_min"]!==null
         && $data["domain_max"]!==null
         && $data["domain_min"] > $data["domain_max"] ) {
            throw new ValidationException( "LinkType", "data", $data, [ "domain_min"=>[ "domain_min can't be greater than domain_max" ] ] );
        }
        if( @$data["range_min"]!==null
         && @$data["range_max"]!==null
         && $data["range_min"] > $data["range_max"] ) {
            throw new ValidationException( "LinkType", "data", $data, [ "range_min"=>[ "range_min can't be greater than range_max" ] ] );
        }
    }


    public function newLink($subject,$object)
    {
        if( $subject->record_type_sid != $this->domain_sid ) {
            throw new ValidationException( "newLink", "subject", $subject->record_type_sid, [ "subject"=>[ "subject of incorrect type for this linktype (expects ".$this->domain_sid.")" ] ] );
        }
        if( $object->record_type_sid != $this->range_sid ) {
            throw new ValidationException( "newLink", "object", $object->record_type_sid, [ "object"=>[ "object of incorrect type for this linktype (expexts ".$this->range_sid.")" ] ] );
        }
   
        $link = new Link();
        $link->documentRevision()->associate( $this->documentRevision );
        $link->link_type_sid = $this->sid;
        $link->subject_sid = $subject->sid;
        $link->object_sid = $object->sid;
        $link->save();
        return $link;
    }
}



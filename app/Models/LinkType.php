<?php

namespace App\Models;

use Illuminate\Support\Facades\Validator;
use App\Exceptions\DataStructValidationException;

/**
 * @property DocumentRevision documentRevision
 * @property int document_revision_id
 * @property array data
 * @property string name
 * @property int sid
 * @property int domain_sid
 * @property int range_sid
 */
class LinkType extends DocumentPart
{
    public function domain() {
        return $this->hasOne( 'App\Models\RecordType', 'sid', 'domain_sid' )->where( 'document_revision_id', $this->document_revision_id );
    }

    public function range() {
        return $this->hasOne( 'App\Models\RecordType', 'sid', 'range_sid' )->where( 'document_revision_id', $this->document_revision_id );
    }

    public function links() {
        return $this->documentRevision->records()->where( "link_type_sid", $this->sid );
    }

    // candidate for a trait or something?
    var $dataCache;
    public function data() {
        if( !$this->dataCache ) { 
            $this->dataCache = json_decode( $this->data, true );
        }  
        return $this->dataCache;
    }

    public function validateName() {

        $validator = Validator::make(
        [ 'name' => $this->name ],
        [ 'name' => 'required|alpha_dash|min:2|max:255' ]);

        if($validator->fails()) {
            throw new DataStructValidationException( "LinkType", "name", $this->name, $validator->errors() );
        }
    }

    public function validateData() {

        $validator = Validator::make(
          $this->data(),
          [ 'domain_min' => 'min:0,integer', 
            'domain_max' => 'min:1,integer',
            'range_min' => 'min:0,integer',
            'range_max' => 'min:1,integer' ] );

        if($validator->fails()) {
            throw new DataStructValidationException( "LinkType", "data", $this->data(), $validator->errors() );
        }

        if( @$this->data()["domain_min"]!==null
         && @$this->data()["domain_max"]!==null
         && $this->data()["domain_min"] > $this->data()["domain_max"] ) {
            throw new DataStructValidationException( "LinkType", "data", $this->data(), [ "domain_min"=>[ "domain_min can't be greater than domain_max" ] ] );
        }
        if( @$this->data()["range_min"]!==null
         && @$this->data()["range_max"]!==null
         && $this->data()["range_min"] > $this->data()["range_max"] ) {
            throw new DataStructValidationException( "LinkType", "data", $this->data(), [ "range_min"=>[ "range_min can't be greater than range_max" ] ] );
        }

        if( @$this->data()["range_min"]==1 && (@$this->data()["range_max"]===null || $this->data()["range_max"]==1 ) 
         && @$this->data()["domain_min"]==1 && (@$this->data()["domain_max"]===null || $this->data()["domain_max"]==1 ) ) {
            throw new DataStructValidationException( "LinkType", "data", $this->data(), [ "range_min"=>[ "range and domain can't both be exactly 1." ] ] );
        }
          
    }

    public function validateLinkSubject( $subject ) {
        if( $subject->record_type_sid != $this->domain_sid ) {
            throw new DataStructValidationException( "Link", "subject", $subject->record_type_sid, [ "subject"=>[ "subject of incorrect type for this linktype (expects ".$this->domain_sid.")" ] ] );
        }
    }
    
    public function validateLinkObject( $object ) { 
        if( $object->record_type_sid != $this->range_sid ) {
            throw new DataStructValidationException( "Link", "object", $object->record_type_sid, [ "object"=>[ "object of incorrect type for this linktype (expexts ".$this->range_sid.")" ] ] );
        }
    }

    public function createLink($subject,$object)
    {
        $this->validateLinkSubject($subject);
        $this->validateLinkObject($object);

        $link = new Link();
        $link->documentRevision()->associate( $this->documentRevision );
        $link->link_type_sid = $this->sid;
        $link->subject_sid = $subject->sid;
        $link->object_sid = $object->sid;
        $link->save();
        return $link;
    }
}



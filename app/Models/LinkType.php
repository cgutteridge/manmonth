<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
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
    /**
     * @return RecordType
     */
    public function domain() {
        return $this->hasOne( 'App\Models\RecordType', 'sid', 'domain_sid' )->where( 'document_revision_id', $this->document_revision_id );
    }

    /**
     * @return RecordType
     */
    public function range() {
        return $this->hasOne( 'App\Models\RecordType', 'sid', 'range_sid' )->where( 'document_revision_id', $this->document_revision_id );
    }

    /**
     * @return Collection List of Record models
     */
    public function links() {
        return $this->documentRevision->records()->where( "link_type_sid", $this->sid );
    }

    /**
     * @throws DataStructValidationException
     */
    public function validateName() {

        $validator = Validator::make(
        [ 'name' => $this->name ],
        [ 'name' => 'required|alpha_dash|min:2|max:255' ]);

        if($validator->fails()) {
            throw new DataStructValidationException( "Validation fail in linktype.name: ".join( ", ", $validator->errors() ));
        }
    }

    /**
     * @throws DataStructValidationException
     */
    public function validateData() {

        $validator = Validator::make(
          $this->data,
          [ 'domain_min' => 'min:0,integer', 
            'domain_max' => 'min:1,integer',
            'range_min' => 'min:0,integer',
            'range_max' => 'min:1,integer' ] );

        if($validator->fails()) {
            throw new DataStructValidationException( "Validation fail in linktype.data: ".join( ", ", $validator->errors() ));
        }

        if( @$this->data["domain_min"]!==null
         && @$this->data["domain_max"]!==null
         && $this->data["domain_min"] > $this->data["domain_max"] ) {
            throw new DataStructValidationException( "Validation fail in linktype.data: domain_min can't be greater than domain_max");
        }
        if( @$this->data["range_min"]!==null
         && @$this->data["range_max"]!==null
         && $this->data["range_min"] > $this->data["range_max"] ) {
            throw new DataStructValidationException( "Validation fail in linktype.data: range_min can't be greater than range_max");
        }

        if( @$this->data["range_min"]==1 && (@$this->data["range_max"]===null || $this->data["range_max"]==1 )
         && @$this->data["domain_min"]==1 && (@$this->data["domain_max"]===null || $this->data["domain_max"]==1 ) ) {
            throw new DataStructValidationException( "Validation fail in linktype.data: range and domain can't be both exactly one as that confuses me.");
        }
          
    }

    /**
     * @param $subject
     * @throws DataStructValidationException
     */
    public function validateLinkSubject($subject ) {
        if( $subject->record_type_sid != $this->domain_sid ) {
            throw new DataStructValidationException( "Validation fail in linktype.subject: incorrect type for this linktype (expects ".$this->domain_sid.")");
        }
    }

    /**
     * @param $object
     * @throws DataStructValidationException
     */
    public function validateLinkObject($object ) {
        if( $object->record_type_sid != $this->range_sid ) {
            throw new DataStructValidationException( "Validation fail in linktype.object: incorrect type for this linktype (expects ".$this->domain_sid.")");
        }
    }

    /**
     * @param $subject
     * @param $object
     * @return Link
     */
    public function createLink($subject, $object)
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



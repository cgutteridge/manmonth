<?php

namespace App\Models;

use App\Exceptions\DataStructValidationException;
use App\MMScript\Values\AbstractValue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


/**
 * @property DocumentRevision documentRevision
 * @property int sid
 * @property RecordType recordType
 * @property int document_revision_id
 * @property string data
 * @property Collection forwardLinks
 * @property int record_type_sid
 */
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

    /**
     * @param string $linkName
     * @return array[Record]
     */
    public function forwardLinkedRecords($linkName) {
        $linkType = $this->documentRevision->linkTypeByName( $linkName );
        $recordIds = DB::table('links')
            ->where("links.document_revision_id", "=", $this->documentRevision->id )
            ->where("links.subject_sid", '=', $this->sid)
            ->where("links.link_type_sid", '=', $linkType->sid)
            ->pluck("links.object_sid");
        $records = [];
        foreach( $recordIds as $recordSid ) {
            $records []= $this->documentRevision->records()->where( 'sid','=', $recordSid )->first();
        }
        return $records;
    }

    /**
     * @param string $linkName
     * @return array[Record]
     */
    public function backLinkedRecords($linkName) {
        $linkType = $this->documentRevision->linkTypeByName( $linkName );
        $recordIds = DB::table('links')
            ->where("links.document_revision_id", "=", $this->documentRevision->id )
            ->where("links.object_sid", '=', $this->sid)
            ->where("links.link_type_sid", '=', $linkType->sid)
            ->pluck("links.subject_sid");
        $records = [];
        foreach( $recordIds as $recordId ) {
            $records []= $this->documentRevision->records()->where( 'sid','=', $recordId )->first();
        }
        return $records;
    }

    // candidate for a trait or something?
    var $dataCache;
    public function data() {
        if( !$this->dataCache ) { 
            $this->dataCache = json_decode( $this->data, true );
        }  
        return $this->dataCache;
    }

    // get the typed value from a field or null
    /**
     * @param string $fieldName
     * @return AbstractValue
     */
    public function getValue($fieldName) {
        return $this->recordType->field( $fieldName )->makeValue( @$this->data()[$fieldName] );
    }
    // return a text representation and all associated records 
    // following subject->object direction links only.
    // does not (yet) worry about loops.
    /**
     * @param string $indent
     * @return string
     */
    function dumpText($indent="") {
        $r = "";
        $r.= $indent."".$this->recordType->name."#".$this->sid." ".$this->data."\n";
        foreach( $this->forwardLinks as $link ) {
             $r.=$indent."  ->".$link->linkType->name."->\n";
             $r.=$link->objectRecord->dumpText( $indent."    " );
        }
        return $r;
    }

    /**
     * @throws DataStructValidationException
     */
    public function validateData() {
        $validationCodes = [];
        foreach( $this->recordType->fields() as $field ) {
            $validationCodes[$field->data("name")] = $field->valueValidationCode();
        }

        $validator = Validator::make( $this->data, $validationCodes );

        if($validator->fails()) {
            throw new DataStructValidationException( "Record", "data", $this->data, $validator->errors() );
        }
    }

}



<?php

namespace App\Models;

use App\Exceptions\DataStructValidationException;
use App\Fields\Field;
use App\MMScript\Values\Value;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


/**
 * @property DocumentRevision documentRevision
 * @property int sid
 * @property RecordType recordType
 * @property int document_revision_id
 * @property string array
 * @property Collection forwardLinks
 * @property Collection backLinks
 * @property int record_type_sid
 * @property array data
 */
class Record extends DocumentPart
{

    /**
     * @return RecordType
     */
    public function recordType()
    {
        return $this->hasOne( 'App\Models\RecordType', 'sid', 'record_type_sid' )
            ->where( 'document_revision_id', $this->document_revision_id );
    }

    /**
     * @return Collection (list of Link)
     */
    public function forwardLinks()
    {
        return $this->hasMany( 'App\Models\Link', 'subject_sid', 'sid' )
            ->where( 'document_revision_id', $this->document_revision_id );
    }

    /**
     * @return Collection (list of Link)
     */
    public function backLinks()
    {
        return $this->hasMany( 'App\Models\Link', 'object_sid', 'sid' )
            ->where( 'document_revision_id', $this->document_revision_id );
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
            $records []= $this->documentRevision->records()->getQuery()
                ->where( 'sid','=', $recordSid )
                ->first();
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

    //
    /**
     * Get the typed value (or null value object) from a field
     * @param string $fieldName
     * @return Value
     */
    public function getValue($fieldName) {
        return $this->recordType->field( $fieldName )->makeValue( @$this->data[$fieldName] );
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
        $r.= $indent."".$this->recordType->name."#".$this->sid." ".json_encode($this->data)."\n";
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
            /** @var Field $field */
            $validationCodes[$field->data["name"]] = $field->valueValidationCode();
        }

        $validator = Validator::make( $this->data, $validationCodes );

        if($validator->fails()) {
            throw new DataStructValidationException( "Validation fail in record.data: ".join( ", ", $validator->errors ));
        }
    }

}



<?php

namespace App\Models;

use App\Exceptions\DataStructValidationException;
use App\Fields\Field;
use App\MMScript\Values\Value;
use Illuminate\Database\Eloquent\Collection;
use Validator;
use DB;

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
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->hasOne( 'App\Models\RecordType', 'sid', 'record_type_sid' )
            ->where( 'document_revision_id', $this->document_revision_id );
    }

    /**
     * @return Collection (list of Link)
     */
    public function forwardLinks()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->hasMany( 'App\Models\Link', 'subject_sid', 'sid' )
            ->where( 'document_revision_id', $this->document_revision_id );
    }

    /**
     * @return Collection (list of Link)
     */
    public function backLinks()
    {
        /** @noinspection PhpUndefinedMethodInspection */
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
            /** @noinspection PhpUndefinedMethodInspection */
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

        /** @var \Illuminate\Validation\Validator $validator */
        $validator = Validator::make( $this->data, $validationCodes );
        if($validator->fails()) {
            throw $this->makeValidationException( $validator );
        }
    }

    public function updateData(array $update) {
        $data = $this->data;
        foreach( $update as $key=>$value ) {
            if( $value !== null ) {
                $data[$key]=$value;
            }
        }
        $this->data = $data;
    }

    /**
     * @return string
     * @throws DataStructValidationException
     */
    public function title() {
        $script = $this->recordType->titleScript();
        if( !$script ) {
            return $this->recordType->name . "#" . $this->sid;
        }

        if( $script->type() != "string" ) {
            throw new DataStructValidationException( "If a record type has a title it should be an MMScript which returns a string. This returned a ".$script->type() );
        }
        $result = $script->execute( ["record"=>$this ]);
        return $result->value;
    }
}



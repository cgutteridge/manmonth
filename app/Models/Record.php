<?php

namespace App\Models;

use App\Exceptions\DataStructValidationException;
use App\Fields\Field;
use App\MMScript\Values\Value;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Validator;

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
        return $this->hasOne('App\Models\RecordType', 'sid', 'record_type_sid')
            ->where('document_revision_id', $this->document_revision_id);
    }

    /**
     * @return Collection (list of Link)
     */
    public function forwardLinks()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->hasMany('App\Models\Link', 'subject_sid', 'sid')
            ->where('document_revision_id', $this->document_revision_id);
    }

    /**
     * @return Collection (list of Link)
     */
    public function backLinks()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->hasMany('App\Models\Link', 'object_sid', 'sid')
            ->where('document_revision_id', $this->document_revision_id);
    }

    /**
     * @param LinkType $linkType
     * @return array[Record]
     */
    public function forwardLinkedRecords($linkType)
    {
        $recordIds = DB::table('links')
            ->where("links.document_revision_id", "=", $this->documentRevision->id)
            ->where("links.subject_sid", '=', $this->sid)
            ->where("links.link_type_sid", '=', $linkType->sid)
            ->pluck("links.object_sid");
        $records = [];
        foreach ($recordIds as $recordSid) {
            $records [] = $this->documentRevision->records()->getQuery()
                ->where('sid', '=', $recordSid)
                ->first();
        }
        return $records;
    }

    /**
     * @param LinkType $linkType
     * @return array [Record]
     */
    public function backLinkedRecords($linkType)
    {
        $recordIds = DB::table('links')
            ->where("links.document_revision_id", "=", $this->documentRevision->id)
            ->where("links.object_sid", '=', $this->sid)
            ->where("links.link_type_sid", '=', $linkType->sid)
            ->pluck("links.subject_sid");
        $records = [];
        foreach ($recordIds as $recordId) {
            /** @noinspection PhpUndefinedMethodInspection */
            $records [] = $this->documentRevision->records()->where('sid', '=', $recordId)->first();
        }
        return $records;
    }

    //
    /**
     * Get the typed value (or null value object) from a field
     * @param string $fieldName
     * @return Value
     */
    public function getValue($fieldName)
    {
        return $this->recordType->field($fieldName)->makeValue(@$this->data[$fieldName]);
    }

    // return a text representation and all associated records 
    // following subject->object direction links only.
    // does not (yet) worry about loops.
    /**
     * @param string $indent
     * @return string
     */
    function dumpText($indent = "")
    {
        $r = "";
        $r .= $indent . "" . $this->recordType->name . "#" . $this->sid . " " . json_encode($this->data) . "\n";
        foreach ($this->forwardLinks as $link) {
            $r .= $indent . "  ->" . $link->linkType->name . "->\n";
            $r .= $link->objectRecord->dumpText($indent . "    ");
        }
        return $r;
    }

    /**
     * @throws DataStructValidationException
     */
    public function validate()
    {
        $validationCodes = [];
        foreach ($this->recordType->fields() as $field) {
            /** @var Field $field */
            $validationCodes[$field->data["name"]] = $field->valueValidationCode();
        }

        /** @var \Illuminate\Validation\Validator $validator */
        $validator = Validator::make($this->data, $validationCodes);
        if ($validator->fails()) {
            throw $this->makeValidationException($validator);
        }
    }


    /**
     * Validate forward links to be added to this record
     *  must be relevant links and a legal number
     *  $links are of the format [ link_name=>[ $record,... ]]
     * @param Record[][] $links
     * @throws DataStructValidationException
     */
    public function validateWithForwardLinks($links)
    {
        $linkTypes = $this->recordType->forwardLinkTypes;
        $unknownLinks = $links; // we'll reduce this list to actually unknown items
        $issues = [];
        foreach ($linkTypes as $linkType) {
            // check domain restrictions
            if (isset($linkType->domain_min)
                && isset($links[$linkType->name])
                && count($links[$linkType->name]) < $linkType->domain_min
            ) {
                $issues [] = "Expected minimum of " . $linkType->domain_min . " forward links of type " . $linkType->title();
            }
            if (isset($linkType->domain_max)
                && isset($links[$linkType->name])
                && count($links[$linkType->name]) > $linkType->domain_max
            ) {
                $issues [] = "Expected maximum of " . $linkType->domain_max . " forward links of type " . $linkType->title();
            }
            // check target object(s) are correct type
            if (isset($links[$linkType->name])) {
                foreach ($links[$linkType->name] as $record) {
                    $linkType->validateLinkObject($record);
                    // TODO check $record can accept this additional incoming link
                }
            }

            unset($unknownLinks[$linkType->name]);
        }
        if (count($unknownLinks)) {
            foreach ($unknownLinks as $linkName => $record) {
                $issues [] = "Attempt to add an invalid link type: $linkName";
            }
        }
        if (count($issues)) {
            throw new DataStructValidationException("Validation fail in recordtype.forwardLinks: " . join(", ", $issues));
        }
    }

    /**
     * Validate back links to be added to this record
     *  must be relevant links and a legal number
     *  $links are of the format [ link_name=>[ $record,... ]]
     * @param $links
     * @throws DataStructValidationException
     */
    public function validateWithBackLinks($links)
    {
        $linkTypes = $this->recordType->backLinkTypes;
        $unknownLinks = $links; // we'll reduce this list to actually unknown items
        $issues = [];
        foreach ($linkTypes as $linkType) {
            // check range restrictions
            if (isset($linkType->range_min)
                && isset($links[$linkType->name])
                && count($links[$linkType->name]) < $linkType->range_min
            ) {
                $issues [] = "Expected minimum of " . $linkType->range_min . " back links of type " . $linkType->title();
            }
            if (isset($linkType->dataCache)
                && isset($links[$linkType->name])
                && count($links[$linkType->name]) > $linkType->range_max
            ) {
                $issues [] = "Expected maximum of " . $linkType->range_max . " back links of type " . $linkType->title();
            }
            // check target subject(s) are correct type
            if (@$links[$linkType->name]) {
                foreach ($links[$linkType->name] as $record) {
                    $linkType->validateLinkSubject($record);
                    // TODO check $record can accept this additional incoming link
                }
            }
            unset($unknownLinks[$linkType->name]);
        }
        if (count($unknownLinks)) {
            foreach ($unknownLinks as $linkName => $record) {
                $issues [] = "Attempt to add an invalid link type: $linkName";
            }
        }
        if (count($issues)) {
            throw new DataStructValidationException("Validation fail in recordtype.backLinks: " . join(", ", $issues));
        }
    }


    public function updateData(array $update)
    {
        $data = $this->data;
        foreach ($update as $key => $value) {
            if ($value !== null) {
                $data[$key] = $value;
            }
        }
        $this->data = $data;
    }

}



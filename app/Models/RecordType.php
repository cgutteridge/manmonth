<?php

namespace App\Models;

use App\Exceptions\MMValidationException;
use App\Exceptions\ScriptException;
use App\Fields\Field;
use App\MMScript;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Validator;

/**
 * @property string name
 * @property string label
 * @property string title_script
 * @property array data
 * @property DocumentRevision documentRevision
 * @property Collection forwardLinkTypes
 * @property Collection backLinkTypes
 * @property Collection records
 * @property string external_table
 * @property string external_key
 * @property string external_local_key
 */
class RecordType extends DocumentPart
{
    /**
     * @var Field[]
     */
    var $fieldsCache;
    var $titleScript;

    /**
     * @return Relation
     */
    public function forwardLinkTypes()
    {
        $relationCode = get_class($this) . "#" . $this->id . "->forwardLinkTypes";
        if (!array_key_exists($relationCode, MMModel::$cache)) {
            /** @noinspection PhpUndefinedMethodInspection */
            MMModel::$cache[$relationCode] = $this->documentRevision->linkTypes()
                ->where("domain_sid", $this->sid);
        }
        return MMModel::$cache[$relationCode];
    }

    /**
     * @return Relation
     */
    public function backLinkTypes()
    {
        $relationCode = get_class($this) . "#" . $this->id . "->backLinkTypes";
        if (!array_key_exists($relationCode, MMModel::$cache)) {
            /** @noinspection PhpUndefinedMethodInspection */
            MMModel::$cache[$relationCode] = $this->documentRevision->linkTypes()
                ->where("range_sid", $this->sid);
        }
        return MMModel::$cache[$relationCode];
    }

    /**
     * @param int $recordSid
     * @return Record
     */
    public function record($recordSid)
    {
        # TODO canidate for a more generic cache code for record from sid.
        $relationCode = get_class($this) . "#" . $this->id . "->record/$recordSid";
        if (!array_key_exists($relationCode, MMModel::$cache)) {
            /** @noinspection PhpUndefinedMethodInspection */
            MMModel::$cache[$relationCode] = $this->documentRevision->records()
                ->where("record_type_sid", $this->sid)
                ->where("sid", (int)$recordSid)->first();
        }
        return MMModel::$cache[$relationCode];
    }

    /**
     * @return Record[]
     */
    public function records()
    {
        $relationCode = get_class($this) . "#" . $this->id . "->records";
        if (!array_key_exists($relationCode, MMModel::$cache)) {
            /** @noinspection PhpUndefinedMethodInspection */
            MMModel::$cache[$relationCode] = $this->documentRevision->records()->where("record_type_sid", $this->sid)->get();
        }
        return MMModel::$cache[$relationCode];
    }

    /**
     * @return ReportType[]
     */
    public function reportTypes()
    {
        $relationCode = get_class($this) . "#" . $this->id . "->reportTypes";
        if (!array_key_exists($relationCode, MMModel::$cache)) {
            /** @noinspection PhpUndefinedMethodInspection */
            MMModel::$cache[$relationCode] = $this->documentRevision->reportTypes()
                ->where("base_record_type_sid", $this->sid)->get();
        }
        return MMModel::$cache[$relationCode];
    }

    /**
     * Data to create the record. Should supply data and all 1:n and n:1 links.
     * may supply other links but this is not requred.
     * 1:1 links are not yet supported.
     * @param array $data
     * @param array $forwardLinks
     * @param array $backLinks
     * @return Record
     */
    public function createRecord($data = [], $forwardLinks = [], $backLinks = [])
    {
        // make any single link targets into a list before validation
        foreach ($forwardLinks as $key => &$value) {
            if (!is_array($value)) {
                $value = [$value];
            }
        }
        foreach ($backLinks as $key => &$value) {
            if (!is_array($value)) {
                $value = [$value];
            }
        }

        // these need to be checked before we create the record
        // there is a good argument for making this validation much
        // smarter and looking and both existing and new links

        $record = new Record();
        $record->data = $data;
        $record->documentRevision()->associate($this->documentRevision);
        $record->record_type_sid = $this->sid;
        $record->validate();
        $record->validateWithForwardLinks($forwardLinks);
        $record->validateWithBackLinks($backLinks);
        $record->save();

        // we've been through validation so assume this is all OK
        foreach ($this->forwardLinkTypes as $linkType) {
            $targets = @$forwardLinks[$linkType->name];
            if ($targets) {
                foreach ($targets as $target) {
                    $linkType->createLink($record, $target);
                }
            }
        }
        foreach ($this->backLinkTypes as $linkType) {
            $targets = @$backLinks[$linkType->name];
            if ($targets) {
                foreach ($targets as $target) {
                    $linkType->createLink($target, $record);
                }
            }
        }

        return $record;
    }

    /**
     * @param string $name
     * @return Field|null
     */
    public function field($name)
    {
        foreach ($this->fields() as $field) {
            if ($field->data["name"] == $name) {
                return $field;
            }
        }
        return null; // no such field
    }

    /**
     * Return the fields that make up this recordType
     * @return Field[]
     */
    public function fields()
    {
        if (!$this->fieldsCache) {
            $this->fieldsCache = [];
            foreach ($this->data["fields"] as $fieldData) {
                $this->fieldsCache [] = Field::createFromData($fieldData, $this);
            }
        }
        return $this->fieldsCache;
    }

    /**
     * @param array $fields
     */
    public function setFields($fields)
    {
        $data = $this->data;
        $data['fields'] = $fields;
        $this->data = $data;
        $this->fieldsCache=null;
    }

    /**
     * Add or update a field
     * @param Field $field
     */
    public function setField(Field $field)
    {
        $data = $this->data;
        $updated = false;
        for( $i=0; $i<sizeof($data["fields"]); $i++ )
        {
            if( $field->data["name"] == $data["fields"][$i]["name"] ) {
                $data["fields"][$i] = $field->data;
                $updated=true;
                break;
            }
        }
        if( !$updated ) {
            // append it to the end
            $data["fields"][] = $field->data;
        }
        $this->data = $data;
        $this->fieldsCache=null;
    }


    /**
     * @throws MMValidationException
     */
    public function validate()
    {
        $validator = Validator::make(
            ['name' => $this->name],
            ['name' => 'required|codename|max:255']);

        if ($validator->fails()) {
            throw $this->makeValidationException($validator);
        }

        $validator = Validator::make(
            $this->data,
            [
                'title' => 'string',
                'fields' => 'required|array',
                'fields.*.type' => 'required|in:boolean,integer,decimal,string,option'
            ]);

        if ($validator->fails()) {
            throw $this->makeValidationException($validator);
        }
        foreach ($this->fields() as $field) {
            $field->validate();
        }

        try {
            $script = $this->titleScript();
        } catch (ScriptException $e) {
            throw new MMValidationException("Error in title script: " . $e->getMessage(), 0, $e);
        }
        if (isset($script)) {
            if ($script->type() != "string") {
                throw new MMValidationException("If a record type has a title it should be an MMScript which returns a string. This returned a " . $script->type());
            }
        }

    }

    /**
     * Compiles the title script, if any, for this recordtype
     * @return MMScript
     */
    function titleScript()
    {
        if (isset($this->titleScript)) {
            return $this->titleScript;
        }
        if (!isset($this->title_script) || trim($this->title_script) == "") {
            return null;
        }
        $this->titleScript = new MMScript(
            $this->title_script,
            $this->documentRevision,
            ["record" => $this, "config" => $this->documentRevision->configRecordType()]);
        return $this->titleScript;
    }

    /**
     * Update this recordType from values in the data
     * @param array $properties
     */
    public function setProperties($properties)
    {
        $this->updateValues($properties);
        if (array_key_exists("data", $properties)) {
            $this->data = $properties["data"];
        }
    }

    /**
     * @param array $update
     */
    public function updateValues(array $update)
    {
        /** @var Field $metaField */
        foreach ($this->metaFields() as $metaField) {
            $name = $metaField->data["name"];
            if (isset($update[$name])) {
                $this->$name = $update[$name];
            }
        }
    }

    /**
     * List of the metadata fields for this field's properties.
     * @return Field[]
     */
    public function metaFields()
    {
        $metaFields = [];
        foreach ($this->metaFieldDefinitions() as $fieldData) {
            $metaFields[] = Field::createFromData($fieldData);
        }
        return $metaFields;
    }

    /**
     * Return the fields that describe the metadata of a recordType
     * @return Field[]
     */
    public function metaFieldDefinitions()
    {
        return [
            [
                "name" => "name",
                "required" => true,
                "type" => "string",
                "label" => "Code name",
                "editable" => false,
            ],
            [
                "name" => "label",
                "type" => "string",
                "label" => "Label"
            ],
            [
                "name" => "title_script",
                "type" => "string",
                "label" => "Title script",
            ],
            [
                "name" => "external_table",
                "type" => "string",
                "label" => "External Data Table"
            ],
            [
                "name" => "external_key",
                "type" => "string",
                "label" => "External Data Key"
            ],
            [
                "name" => "external_local_key",
                "type" => "string",
                "label" => "External Data Local Key"
            ],
        ];
    }

    /**
     * Return an array of the columns in the primary linked external database table.
     * Incuding the key column.
     * But not columns linked to other tables.
     * @return array
     */
    public function externalColumns()
    {
        if( !isset( $this->external_table )){
            return [];
        }
        $external_fields = [];
        $external_fields[] = $this->external_key;
        foreach ($this->fields() as $field) {
            if (array_key_exists('external_column', $field->data)
                && !empty($field->data['external_column'])
                && empty($field->data['external_table'])
            ) {
                $external_fields[] = $field->data['external_column'];
            }   
        }
        return $external_fields;
    }

    /**
     * This function indicates if there is a linked external
     * data table that can be used to create new records from.
     * @return bool
     */
    public function isLinkedToExternalData()
    {
        if( empty( $this->external_table)) {
            return false;
        }
        return true;
    }

    public function metaValues()
    {
        $metavalues = [];
        foreach ($this->metaFields() as $field) {
            $name = $field->data["name"];
            $metavalues[$name] = $this->$name;
        }
        return $metavalues;
    }
}



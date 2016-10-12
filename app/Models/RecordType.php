<?php

namespace App\Models;

use App\Exceptions\DataStructValidationException;
use App\Fields\Field;
use App\MMScript;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Validator;

// TODO - sort out exception throwing

/**
 * @property string name
 * @property string label
 * @property string title_script
 * @property array data
 * @property DocumentRevision documentRevision
 * @property Collection forwardLinkTypes
 * @property Collection backLinkTypes
 * @property Collection records
 * @property Collection reportTypes
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
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->documentRevision->linkTypes()
            ->where("domain_sid", $this->sid);
    }

    /**
     * @return Relation
     */
    public function backLinkTypes()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->documentRevision->linkTypes()
            ->where("range_sid", $this->sid);
    }

    // TODO: passing in secondary records could be helpful later

    /**
     * @return Record[]
     */
    public function records()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->documentRevision->records()
            ->where("record_type_sid", $this->sid);
    }

    /**
     * @return ReportType[]
     */
    public function reportTypes()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->documentRevision->reportTypes()
            ->where("base_record_type_sid", $this->sid);
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
     * @return Field[]
     */
    public function fields()
    {
        if (!$this->fieldsCache) {
            $this->fieldsCache = [];
            foreach ($this->data["fields"] as $fieldData) {
                $this->fieldsCache [] = Field::createFromData($fieldData);
            }
        }
        return $this->fieldsCache;
    }

    /**
     * @throws DataStructValidationException
     */
    public function validate()
    {
        $validator = Validator::make(
            ['name' => $this->name],
            ['name' => 'required|codename|max:255']);

        if ($validator->fails()) {
            $this->makeValidationException($validator);
        }

        $validator = Validator::make(
            $this->data,
            [
                'title' => 'string',
                'fields' => 'required|array',
                'fields.*.type' => 'required|in:boolean,integer,decimal,string'
            ]);

        if ($validator->fails()) {
            throw new DataStructValidationException("RecordType", "data", $this->data, $validator->errors());
        }
        foreach ($this->fields() as $field) {
            $field->validate();
        }

        if (isset($this->data["title"])) {

            $script = $this->titleScript();
            if ($script->type() != "string") {
                throw new DataStructValidationException("If a record type has a title it should be an MMScript which returns a string. This returned a " . $script->type());
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
            ["record" => $this]);
        return $this->titleScript;
    }


    /**
     * Update this recordType from values in the data
     * @param array $properties
     */
    public function setProperties($properties)
    {
        if (array_key_exists("label", $properties)) {
            $this->label = $properties["label"];
        }
        if (array_key_exists("title_script", $properties)) {
            $this->title_script = $properties["title_script"];
        }
        if (array_key_exists("data", $properties)) {
            $this->data = $properties["data"];
        }
    }

}



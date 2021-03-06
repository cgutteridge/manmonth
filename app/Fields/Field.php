<?php

namespace App\Fields;

use App\Exceptions\MMValidationException;
use App\Exceptions\ScriptException;
use App\MMScript;
use App\MMScript\Values\Value;
use App\Models\RecordType;
use Exception;
use Validator;

abstract class Field
{
    // need to make this non static? Maybe by making a fieldFactory singleton
    public $data;
    public $recordType;
    protected $script;

    // this isn't written to the db so don't bother making data a json_encoded
    // param... but this is inconsistant with DocumentPart models.

    /**
     * Field constructor.
     * @param $data
     * @param RecordType $recordType
     */
    public function __construct($data, RecordType $recordType = null)
    {
        $this->data = $data;
        $this->recordType = $recordType;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->data["name"];
    }

    /**
     * Return the laravel validate code to validate a value for this field
     * Subclassed by non abstract versions of Field
     * @return string
     */
    public function valueValidationCode()
    {
        $parts = array_keys($this->valueValidationCodeParts());
        sort($parts); // so tests don't get confused by meaningless variation
        return join("|", $parts);
    }

    /**
     * @return array
     */
    protected function valueValidationCodeParts()
    {
        $parts = [];
        if (@$this->data["required"]) {
            $parts["required"] = true;
        }
        return $parts;
    }

    /**
     * Is this a required field?
     * @return bool
     */
    public function required()
    {
        return (true == @$this->data["required"]);
    }

    /**
     * Give the description text for the field, or null if there is none.
     * @return string|null
     */
    public function description()
    {
        if (@$this->data["description"]) {
            return $this->data["description"];
        }
        return null;
    }

    /**
     * Check this field is valid
     * @throws MMValidationException
     */
    public function validate()
    {
        $validator = Validator::make($this->data, $this->fieldValidationArray());
        if ($validator->fails()) {
            throw $this->validationException(join(", ", $validator->errors()->all()));
        }

        /* if there's a script, check that the script will return the right type .*/
        if (isset($this->data["script"])) {
            if ($this->recordType == null) {
                throw $this->validationException("Script can't be tested because the field doesn't belong to a record type.");
            }
            try {
                $script = $this->getScript($this->recordType);
                if ($script->type() != $this->data["type"]) {
                    throw $this->validationException("Script should return a '" . $this->data["type"] . "' but returned a " . $script->type());
                }
            } catch (ScriptException $e) {
                throw $this->validationException("Script has a problem: " . $e->getMessage());
            }
        }
    }

    /**
     * Return the laravel validate array to validate data for this field
     * @return array
     */
    public function fieldValidationArray()
    {
        return [
            'name' => 'required|codename|min:2|max:255',
            'label' => 'string',
            'description' => 'string',
            'required' => 'boolean',
            'mode' => 'string|in:prefer_local,prefer_external,only_local,only_external',
            'script' => 'string'
        ];
    }

    /**
     * Builds a nice exception with context.
     * @param string $msg
     * @return MMValidationException
     */
    protected function validationException($msg)
    {
        $prefix = "Validation failure in ";
        if ($this->recordType) {
            $prefix .= "record type \"" . $this->recordType->label . "\", ";
        }
        if (array_key_exists("name", $this->data)) {
            $prefix .= "field \"" . $this->data["name"] . "\": ";
        } else {
            $prefix .= "field with no name defined: ";
        }
        return new MMValidationException($prefix . $msg);
    }

    /**
     * Give the compiled MMScript for this field, or null if it's a simple field.
     * @param RecordType $recordType
     * @return MMScript|null
     */
    public function getScript(RecordType $recordType)
    {
        if (!isset($this->script)) {
            $documentRevision = $recordType->documentRevision;
            $this->script = new MMScript(
                $this->data["script"],
                $documentRevision,
                ["record" => $recordType, "config" => $documentRevision->configRecordType()]);
        }

        return $this->script;
    }

    /**
     * Makes a MMScript value of this field type.
     * @param $value
     * @return Value
     */
    public abstract function makeValue($value);

    /**
     * List of the metadata fields for this field's properties.
     * @return Field[]
     * @throws Exception
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
     * Gives a list of field descriptions for the properties of this field.
     */
    protected function metaFieldDefinitions()
    {
        return [
            [
                "name" => "type",
                "required" => true,
                "type" => "string",
                "label" => "Field type",
                "editable" => false,
            ],
            [
                "name" => "label",
                "type" => "string",
                "label" => "Label"
            ],
            [
                "name" => "protected",
                "type" => "boolean",
                "default" => false,
                "editable" => false,
                "label" => "Schema protected"
            ],
            [
                "name" => "editable",
                "type" => "boolean",
                "default" => true,
                "label" => "Field value can be altered",
                "editable" => false
            ],
            [
                "name" => "description",
                "type" => "string",
                "label" => "Description"
            ],
            [
                "name" => "required",
                "type" => "boolean",
                "label" => "Required",
                "default" => false
            ],
            [
                "name" => "mode",
                "type" => "option",
                "label" => "Data mode",
                "options" => "prefer_local|Use local data, failing that use external data\nprefer_external|Use external data, failing that use local data\nonly_local|Only use local data\nonly_external|Only use external data"
            ],
            [
                "name" => "script",
                "type" => "string", # should be a special type later on.
                "label" => "Calculated value script",
                # "config" => $documentRevision->configRecordType()
            ],
            [
                "name" => "external_column",
                "type" => "string",
                "label" => "External data column"
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
            [
                "name" => "prefix",
                "type" => "string",
                "label" => "Prefix text"
            ],
            [
                "name" => "suffix",
                "type" => "string",
                "label" => "Suffix text"
            ]

        ];
    }

    /**
     * @param array $fieldData
     * @param RecordType $recordType
     * @return Field
     * @throws Exception
     */
    public static function createFromData($fieldData, RecordType $recordType = null)
    {
        if ($fieldData["type"] == "string") {
            return new StringField($fieldData, $recordType);
        } elseif ($fieldData["type"] == "longtext") {
            return new LongTextField($fieldData, $recordType);
        } elseif ($fieldData["type"] == "decimal") {
            return new DecimalField($fieldData, $recordType);
        } elseif ($fieldData["type"] == "integer") {
            return new IntegerField($fieldData, $recordType);
        } elseif ($fieldData["type"] == "boolean") {
            return new BooleanField($fieldData, $recordType);
        } elseif ($fieldData["type"] == "option") {
            return new OptionField($fieldData, $recordType);
        } elseif ($fieldData["type"] == "record") {
            return new RecordField($fieldData, $recordType);
        } else {
            throw new Exception("Unknown field type: '" . $fieldData["type"] . "'");
        }
    }

    /**
     * True if the value of this field can be edited.
     * (not the properties of the field)
     * @return bool
     */
    public function editable()
    {
        if ($this->getMode() == "only_external") {
            return false;
        }
        if ($this->hasScript()) {
            // can't edit calculated fields
            return false;
        }
        if (!isset($this->data["editable"])) {
            return true;
        }
        return (true == $this->data["editable"]);
    }

    /**
     * Get the code indicating if the priority data source for this field is
     * the local database or imported data.
     * @return string
     */
    public function getMode()
    {
        if (isset($this->data["mode"])) {
            return $this->data["mode"];
        }
        if (isset($this->data['external'])) {
            return "prefer_local";
        }
        return "only_local";
    }

    /**
     * True if the field has a script to caclulate it's value.
     * @return bool
     */
    public function hasScript()
    {
        return (isset($this->data["script"]));
    }


    /**
     * @param array $update
     */
    public function updateData(array $update)
    {
        $data = $this->data;
        foreach ($update as $key => $value) {
            if ($value !== null) {
                $data[$key] = ($value == "" ? null : $value);

            }
        }
        $this->data = $data;
    }

}


<?php

namespace App\Fields;

use App\Exceptions\MMValidationException;
use App\MMScript\Values\Value;
use Exception;
use Validator;

abstract class Field
{
    // need to make this non static? Maybe by making a fieldFactory singleton
    public $data;

    /**
     * Field constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    // this isn't written to the db so don't bother making data a json_encoded
    // param... but this is inconsistant with DocumentPart models.

    /**
     * @param array $fieldData
     * @return Field
     * @throws Exception
     */
    public static function createFromData($fieldData)
    {
        if ($fieldData["type"] == "string") {
            return new StringField($fieldData);
        } elseif ($fieldData["type"] == "decimal") {
            return new DecimalField($fieldData);
        } elseif ($fieldData["type"] == "integer") {
            return new IntegerField($fieldData);
        } elseif ($fieldData["type"] == "boolean") {
            return new BooleanField($fieldData);
        } elseif ($fieldData["type"] == "option") {
            return new OptionField($fieldData);
        } elseif ($fieldData["type"] == "record") {
            return new RecordField($fieldData);
        } else {
            throw new Exception("Unknown field type: '" . $fieldData["type"] . "'");
        }
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
            throw new MMValidationException("Validation fail in field: " . join(", ", $validator->errors()->all()));
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
        ];
    }

    /**
     * Makes a MMScript value of this field type.
     * @param $value
     * @return Value
     */
    public abstract function makeValue($value);

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
}


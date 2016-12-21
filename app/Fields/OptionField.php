<?php

namespace App\Fields;

use App\Exceptions\MMValidationException;
use App\MMScript\Values\NullValue;
use App\MMScript\Values\StringValue;

class OptionField extends Field
{

    /**
     * @return array
     */
    public function fieldValidationArray()
    {
        return array_merge(parent::fieldValidationArray(), [
            'type' => 'required|in:option',
            'options' => 'required|string',
            'default' => 'string',
        ]);
    }

    /**
     * @throws MMValidationException
     */
    public function validate()
    {
        parent::validate();

        // options should be a list of terms serparated with newlines
        // terms should not be repeated
        // any text after a pipe (with optional whitespace around it) is treated as a label.
        // labels should not repeat
        // codes should not contain commas
        // codes *may* be a zero length string only if with a label eg. "|Not set"
        // empty lines or just "|" by itself is ignored.
        // the order of codes matters.
        $labels = [];
        $codes = [];
        $options = preg_split("/\n/", $this->data['options']);
        foreach ($options as $option) {
            if (preg_match('/\|/', $option)) {
                list($code, $label) = preg_split('/\|/', $option, 2);
            } else {
                $code = $option;
                $label = $option;
            }
            $label = trim($label);
            $code = trim($code);
            if ($code == '' && $label == '') {
                continue; // blank lines or lines with just | are ignored.
            }
            if (array_key_exists($code, $codes)) {
                throw new MMValidationException("Repeated use of option code '$code'");
            }
            $codes[$code] = true;
            if (array_key_exists($label, $labels)) {
                throw new MMValidationException("Repeated use of option label '$label'");
            }
            $labels[$label] = true;
            if (preg_match('/,/', $code)) {
                throw new MMValidationException("Option code '$code' contains a forbidden comma");
            }

        }
        if (sizeof($codes) == 0) {
            throw new MMValidationException("You must specify at least one option");
        }
    }

    /**
     * @param string $value
     * @return NullValue|StringValue
     */
    public function makeValue($value)
    {
        if (!isset($value)) {
            if (isset($this->data["default"])) {
                return new StringValue($this->data["default"]);
            }
            return new NullValue();
        }
        return new StringValue($value);
    }

    /**
     * @return array
     */
    protected function valueValidationCodeParts()
    {
        $parts = parent::valueValidationCodeParts();
        $parts["string"] = true;
        $parts["in:" . join(",", $this->options())] = true;
        return $parts;
    }

    /**
     * @return array list of the valid codes for this option field
     */
    public function options()
    {
        return array_keys($this->optionsWithLabels());
    }

    /**
     * @return array mapping of valid codes to labels
     */
    public function optionsWithLabels()
    {
        if (!array_key_exists('options', $this->data)) {
            // should never happen, but it's not this function's
            // job to throw the exceptions
            return [];
        }
        $options = preg_split("/\n/", $this->data['options']);
        $rv = [];
        foreach ($options as $option) {
            if (preg_match('/\|/', $option)) {
                list($code, $label) = preg_split('/\|/', $option, 2);
            } else {
                $code = $option;
                $label = $option;
            }
            $label = trim($label);
            $code = trim($code);
            if ($code == '' && $label == '') {
                continue;
            }
            $rv[$code] = $label;
        }
        return $rv;
    }
}


<?php
/**
 * Created by PhpStorm.
 * User: cjg
 * Date: 19/09/2016
 * Time: 14:53
 */

namespace App\Http;

// singleton for getting data from web forms
use Illuminate\Http\Request;

class RequestProcessor
{

    /**
     * RequestProcessor constructor.
     * All these methods need access to request so we might as well load it in the
     * constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * This method is the compliment to the link/form template.
     * @param $idPrefix
     * @return array
     */
    public function fromLinkRequest($idPrefix = "")
    {
        $data = [];
        $value = $this->request->get($idPrefix . "subject");
        if ($value !== null) {
            $data["subject"] = $value;
        } else {
            $value = $this->request->old($idPrefix . "subject");
            if ($value !== null) {
                $data["subject"] = $value;
            }
        }
        $value = $this->request->get($idPrefix . "object");
        if ($value !== null) {
            $data["object"] = $value;
        } else {
            $value = $this->request->old($idPrefix . "object");
            if ($value !== null) {
                $data["object"] = $value;
            }
        }
        return $data;
    }

    /**
     * @param null|string $otherwise
     * @return null|string
     */
    public function returnURL($otherwise = null)
    {
        $value = $this->request->get("_mmreturn");
        if (!empty($value)) {
            return $value;
        }
        return $this->request->old("_mmreturn", $otherwise);
    }

    /**
     * This method is the compliment to the editFields.blade template.
     * @param array $fields
     * @param string $idPrefix
     * @return array
     */
    public function fromFieldsRequest(array $fields, $idPrefix = "")
    {
        return $this->_fromFieldsRequest($fields, $idPrefix,
            function (Request $request, $param) {
                return $request->get($param);
            }
        );
    }

    /**
     * This method is the compliment to the editFields.blade template.
     * @param array $fields
     * @param $idPrefix
     * @param callable $getParam
     * @return array
     */
    protected function _fromFieldsRequest(array $fields, $idPrefix, callable $getParam)
    {
        $data = [];
        foreach ($fields as $field) {
            $fieldId = $idPrefix . $field->data["name"];

            //  TODO candidate for classes, but only boolean is a special case SO FAR....
            if ($field->data["type"] == 'boolean') {
                if ($getParam($this->request, $fieldId . "_exists")) {
                    // set to a boolean
                    $data[$field->data["name"]] = true == $getParam($this->request, $fieldId);
                }
                continue;
            }

            $value = $getParam($this->request, $fieldId);
            if ($value !== null) {
                $data[$field->data["name"]] = $value;
            }
        }
        return $data;
    }

    /**
     * This method is the compliment to the editFields.blade template, but
     * uses old() instead of get() to get values.
     * @param array $fields
     * @param string $idPrefix
     * @return array
     */
    public function fromOldFieldsRequest(array $fields, $idPrefix = "")
    {
        return $this->_fromFieldsRequest($fields, $idPrefix,
            function (Request $request, $param) {
                return $request->old($param);
            }
        );
    }

    /**
     * Pull the additions and removals requested to this link from this form.
     * This is a compliment to the editField/link template
     * @param string $idPrefix
     * @return array
     */
    public function fromLinkFieldRequest($idPrefix = "")
    {
        $result = ["add" => [], "remove" => []];
        $gets = $this->request->all();
        foreach ($gets as $key => $value) {
            if (preg_match('/^' . $idPrefix . 'remove_(\d+)$/', $key, $bits) && $value) {
                $result["remove"][$bits[1]] = true;
            }
            if (preg_match('/^' . $idPrefix . 'add_(\d+)$/', $key, $bits) && $value) {
                $result["add"][$bits[1]] = true;
            }
        }
        $result["add"] = array_keys($result["add"]);
        $result["remove"] = array_keys($result["remove"]);

        return $result;
    }
}
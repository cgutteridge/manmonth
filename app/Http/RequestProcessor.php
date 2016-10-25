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
     * This method is the compliment to the link.blade template.
     * @param Request $request
     * @param $idPrefix
     * @return array
     */
    public function fromLinkRequest(Request $request, $idPrefix = "")
    {
        $data = [];
        $value = $request->get($idPrefix . "subject");
        if ($value !== null) {
            $data["subject"] = $value;
        } else {
            $value = $request->old($idPrefix . "subject");
            if ($value !== null) {
                $data["subject"] = $value;
            }
        }
        $value = $request->get($idPrefix . "object");
        if ($value !== null) {
            $data["object"] = $value;
        } else {
            $value = $request->old($idPrefix . "object");
            if ($value !== null) {
                $data["object"] = $value;
            }
        }
        return $data;
    }

    /**
     * @param Request $request
     * @param null|string $otherwise
     * @return null|string
     */
    public function returnURL(Request $request, $otherwise = null)
    {
        $value = $request->get("_mmreturn");
        if ($value !== null) {
            return $value;
        }
        return $request->old("_mmreturn", $otherwise);
    }

    /**
     * This method is the compliment to the editFields.blade template.
     * @param Request $request
     * @param array $fields
     * @param string $idPrefix
     * @return array
     */
    public function fromFieldsRequest(Request $request, array $fields, $idPrefix = "")
    {
        return $this->_fromFieldsRequest($request, $fields, $idPrefix,
            function (Request $request, $param) {
                return $request->get($param);
            }
        );
    }

    /**
     * This method is the compliment to the editFields.blade template.
     * @param Request $request
     * @param array $fields
     * @param $idPrefix
     * @param callable $getParam
     * @return array
     */
    protected function _fromFieldsRequest(Request $request, array $fields, $idPrefix, callable $getParam)
    {
        $data = [];
        foreach ($fields as $field) {
            $fieldId = $idPrefix . $field->data["name"];

            //  TODO candidate for classes, but only boolean is a special case SO FAR....
            if ($field->data["type"] == 'boolean') {
                if ($getParam($request, $fieldId . "_exists")) {
                    // set to a boolean
                    $data[$field->data["name"]] = true == $getParam($request, $fieldId);
                }
                continue;
            }

            $value = $getParam($request, $fieldId);
            if ($value !== null) {
                $data[$field->data["name"]] = $value;
            }
        }
        return $data;
    }

    /**
     * This method is the compliment to the editFields.blade template, but
     * uses old() instead of get() to get values.
     * @param Request $request
     * @param array $fields
     * @param string $idPrefix
     * @return array
     */
    public function fromOldFieldsRequest(Request $request, array $fields, $idPrefix = "")
    {
        return $this->_fromFieldsRequest($request, $fields, $idPrefix,
            function (Request $request, $param) {
                return $request->old($param);
            }
        );
    }
}
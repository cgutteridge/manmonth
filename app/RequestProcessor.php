<?php
/**
 * Created by PhpStorm.
 * User: cjg
 * Date: 19/09/2016
 * Time: 14:53
 */

namespace App;

// singleton for getting data from web forms
use Illuminate\Http\Request;

class RequestProcessor
{
    /**
     * This method is the compliment to the editFields.blade template.
     * @param Request $request
     * @param array $fields
     * @param string $idPrefix
     * @return array
     */
    public function fromRequest(Request $request, array $fields, $idPrefix = "")
    {
        return $this->_fromRequest($request, $fields, $idPrefix,
            function (Request $request, $param) {
                return $request->get($param);
            }
        );
    }

    /**
     * This method is the compliment to the editFields.blade template, but
     * uses old() instead of get() to get values.
     * @param Request $request
     * @param array $fields
     * @param string $idPrefix
     * @return array
     */
    public function fromOldRequest(Request $request, array $fields, $idPrefix = "")
    {
        return $this->_fromRequest($request, $fields, $idPrefix,
            function (Request $request, $param) {
                return $request->old($param);
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
    protected function _fromRequest(Request $request, array $fields, $idPrefix, callable $getParam)
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
}
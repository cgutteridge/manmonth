<?php
/**
 * Created by PhpStorm.
 * User: cjg
 * Date: 19/09/2016
 * Time: 14:53
 */

namespace App\Http;

// singleton for getting data from web forms
use App\Models\Record;
use App\Models\RecordType;
use Illuminate\Http\Request;

/**
 * @property Request request
 * @property TitleMaker titleMaker
 * @property bool old
 */
class RequestProcessor
{

    /**
     * RequestProcessor constructor.
     * All these methods need access to request so we might as well load it in the
     * constructor.
     * @param Request $request
     * @param TitleMaker $titleMaker
     */
    public function __construct(Request $request, TitleMaker $titleMaker)
    {
        $this->request = $request;
        $this->titleMaker = $titleMaker;
        // old keeps track of if there *is* an old request or not
        $this->old = (count($request->old()) > 0);
    }

    /**
     * This method is the compliment to the link/form template.
     * @param string $idPrefix
     * @return array
     */
    public function fromLinkRequest($idPrefix = "")
    {
        $data = [];
        $value = $this->get($idPrefix . "subject");
        if ($value !== null) {
            $data["subject"] = $value;
        }
        $value = $this->get($idPrefix . "object");
        if ($value !== null) {
            $data["object"] = $value;
        }
        return $data;
    }

    /**
     * @param string $term
     * term
     * @param string|null $otherwise
     * @return string
     */
    public function get($term, $otherwise = null)
    {
        if ($this->old) {
            return $this->request->old($term, $otherwise);
        }
        return $this->request->get($term, $otherwise);
    }

    /**
     * @param null|string $otherwise
     * @return null|string
     */
    public function returnURL($otherwise = null)
    {
        return $this->get("_mmreturn", $otherwise);
    }

    /**
     * This method is the compliment to the editFields.blade template.
     * @param array $fields
     * @param string $idPrefix
     * @return array
     */
    public function fromFieldsRequest(array $fields, $idPrefix)
    {
        $data = [];
        foreach ($fields as $field) {
            $fieldId = $idPrefix . $field->data["name"];
            $value = $this->get($fieldId);

            // This code is a candidate for classes per field type,
            // but only boolean is a special case SO FAR....
            if ($field->data["type"] == 'boolean') {
                $exists = $this->get($fieldId . "_exists");
                if ($exists) {
                    // set to a boolean
                    $data[$field->data["name"]] = (true == $value);
                }
                continue;
            }

            if ($value !== null) {
                $data[$field->data["name"]] = $value;
            }
        }
        return $data;
    }

    /**
     * get all the requested changes to links on this record.
     * @param RecordType $recordType
     * @return array
     */
    public function getLinkChanges($recordType)
    {
        $allLinkChanges = ["fwd" => [], "bck" => []];
        foreach ($recordType->forwardLinkTypes as $linkType) {
            // only default types of link are handled on a record update
            if (isset($linkType->range_type)) {
                continue;
            }
            $allLinkChanges["fwd"][$linkType->sid] = $this->fromLinkFieldRequest($linkType->range(), "link_fwd_" . $linkType->sid . "_");
        }
        foreach ($recordType->backLinkTypes as $linkType) {
            // only default types of link are handled on a record update
            if (isset($linkType->domain_type)) {
                continue;
            }
            $allLinkChanges["bck"][$linkType->sid] = $this->fromLinkFieldRequest($linkType->domain(), "link_bck_" . $linkType->sid . "_");
        }
        return $allLinkChanges;
    }

    /**
     * Pull the additions and removals requested to this link from this form.
     * This is a compliment to the editField/link template.
     * For additions
     * @param RecordType $recordType
     * @param string $idPrefix
     * @return array
     * @throws \App\Exceptions\MMValidationException
     */
    public function fromLinkFieldRequest(RecordType $recordType, $idPrefix = "")
    {
        $result = ["add" => [], "remove" => []];
        $gets = $this->all();
        foreach ($gets as $key => $value) {
            if (preg_match('/^' . $idPrefix . 'remove_(\d+)$/', $key, $bits) && $value) {
                $id = $bits[1];
                $result["remove"][$id] = true;
            }
            if (preg_match('/^' . $idPrefix . 'add_(\d+)$/', $key, $bits) && $value) {
                $id = $bits[1];
                // get the record and ensure it's the right type.
                $record = Record::where( 'id',$id )
                    ->where( 'record_type_id', $recordType->id)
                    ->first();
                if ($record) {
                    $result["add"][$id] = $this->titleMaker->title($record);
                    # could give a warning if record is NULL
                }
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function all()
    {
        if ($this->old) {
            return $this->request->old();
        }
        return $this->request->all();
    }

    public function filters()
    {
        $gets = $this->all();
        $filters = [];
        foreach ($gets as $key => $value) {
            if (preg_match('/^filter_(.*)$/', $key, $parts)) {
                $filters[$parts[1]] = $value;
            }
        }
        return $filters;
    }


}

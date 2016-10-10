<?php

namespace App\Models;

use App\Exceptions\DataStructValidationException;
use App\Http\TitleMaker;
use Illuminate\Database\Eloquent\Collection;
use Validator;

/**
 * @property DocumentRevision documentRevision
 * @property int document_revision_id
 * @property array data
 * @property string name
 * @property string label
 * @property mixed inverse_label
 * @property int sid
 * @property int domain_sid
 * @property int range_sid
 * @property int domain_min
 * @property int domain_max
 * @property int range_min
 * @property int range_max
 * @property mixed domain
 * @property mixed range
 */
class LinkType extends DocumentPart
{
    /* defaults */
    protected $attributes = array(
        'domain_min' => 0,
        'range_min' => 0
    );

    /**
     * @return RecordType
     */
    public function domain()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->hasOne('App\Models\RecordType', 'sid', 'domain_sid')->where('document_revision_id', $this->document_revision_id);
    }

    /**
     * @return RecordType
     */
    public function range()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->hasOne('App\Models\RecordType', 'sid', 'range_sid')->where('document_revision_id', $this->document_revision_id);
    }

    /**
     * @return Collection List of Record models
     */
    public function links()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->documentRevision->links()->where("link_type_sid", $this->sid);
    }

    /**
     * @param $subject
     * @param $object
     * @return Link
     */
    public function createLink($subject, $object)
    {
        $this->validateLinkSubject($subject);
        $this->validateLinkObject($object);

        $link = new Link();
        $link->documentRevision()->associate($this->documentRevision);
        $link->link_type_sid = $this->sid;
        $link->subject_sid = $subject->sid;
        $link->object_sid = $object->sid;

        $this->validate();
        $link->save();

        return $link;
    }

    /**
     * @param $subject
     * @throws DataStructValidationException
     */
    public function validateLinkSubject($subject)
    {
    }

    /**
     * @param $object
     * @throws DataStructValidationException
     */
    public function validateLinkObject($object)
    {
    }

    /**
     * @throws DataStructValidationException
     */
    public function validate()
    {
        // TODO check for duplicate codenames
        $validator = Validator::make(
            [
                'name' => $this->name,
                'domain_min' => $this->domain_min,
                'domain_max' => $this->domain_max,
                'range_min' => $this->range_min,
                'range_max' => $this->range_max
            ],
            [
                'name' => 'required|codename|max:255',
                'domain_min' => 'required|min:0,integer',
                'domain_max' => 'min:1,integer',
                'range_min' => 'required|min:0,integer',
                'range_max' => 'min:1,integer'
            ]);

        if ($validator->fails()) {
            throw $this->makeValidationException($validator);
        }

        if (isset($this->domain_min) && isset($this->domain_max)
            && $this->domain_min > $this->domain_max
        ) {
            throw new DataStructValidationException("Validation fail in linktype.data: domain_min can't be greater than domain_max");
        }
        if (isset($this->range_min) && isset($this->range_max)
            && $this->range_min > $this->range_max
        ) {
            throw new DataStructValidationException("Validation fail in linktype.data: range_min can't be greater than range_max");
        }

        if (isset($this->domain_max)
            && isset($this->range_max)
            && $this->range_max == 1 && $this->domain_max == 1
        ) {
            throw new DataStructValidationException("Validation fail in linktype.data: range_max and domain_max can't be both one as that confuses me.");
        }

    }

    /**
     * Update this LinkType from values in the data
     * @param array $properties
     */
    public function setProperties($properties)
    {
        // defaults to zero if empty or null is passed in, but not
        // if it's not set. Fun for testing! Much varied null. Wow!
        if (array_key_exists("domain_min", $properties)) {
            if (isset($properties["domain_min"]) && $properties["domain_min"] !== null) {
                $this->domain_min = $properties["domain_min"];

            } else {
                $this->domain_min = 0;
            }
        }

        if (array_key_exists("domain_max", $properties)) {
            $this->domain_max = $properties["domain_max"];
        }

        if (array_key_exists("range_min", $properties)) {
            if (isset($properties["range_min"]) && $properties["range_min"] !== null) {
                $this->range_min = $properties["range_min"];
            } else {
                $this->range_min = 0;
            }
        }

        if (array_key_exists("range_max", $properties)) {
            $this->range_max = $properties["range_max"];
        }

        if (array_key_exists("label", $properties)) {
            $this->label = $properties["label"];
        }

        if (array_key_exists("inverse_label", $properties)) {
            $this->inverse_label = $properties["inverse_label"];
        }
    }

    /**
     * Return the most human readable title available for the inverse of this link.
     * @return string
     */
    function inverseTitle()
    {
        $titleMaker = new TitleMaker();
        if (isset($this->inverse_label) && trim($this->inverse_label) != "") {
            return $this->inverse_label;
        }
        return "is " . $titleMaker->title($this) . " of";
    }
}



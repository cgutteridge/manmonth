<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Validator;
use App\Exceptions\DataStructValidationException;

/**
 * @property DocumentRevision documentRevision
 * @property int document_revision_id
 * @property array data
 * @property string name
 * @property string title
 * @property int sid
 * @property int domain_sid
 * @property int range_sid
 * @property int domain_min
 * @property int domain_max
 * @property int range_min
 * @property int range_max
 */
class LinkType extends DocumentPart
{
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
        return $this->documentRevision->records()->where("link_type_sid", $this->sid);
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
                'domain_min' => 'min:0,integer',
                'domain_max' => 'min:1,integer',
                'range_min' => 'min:0,integer',
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
     * Return the most human readable title available.
     * @return string
     */
    function bestTitle()
    {
        if (isset($this->title) && trim($this->title) != "") {
            return $this->title;
        }
        return $this->name;
    }
}



<?php

namespace App\Models;

use App\Exceptions\DataStructValidationException;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Link
 * @property int document_revision_id
 * @property int link_type_sid
 * @property int subject_sid
 * @property int object_sid
 * @property Record subjectRecord
 * @property LinkType linkType
 * @property Record objectRecord
 * @package App\Models
 */
class Link extends DocumentPart
{
    /**
     * @return HasOne
     */
    public function linkType()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->hasOne('App\Models\LinkType', 'sid', 'link_type_sid')
            ->where('document_revision_id', $this->document_revision_id);
    }

    /**
     * @return Record
     */
    public function subjectRecord()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->hasOne('App\Models\Record', 'sid', 'subject_sid')
            ->where('document_revision_id', $this->document_revision_id);
    }

    /**
     * @return Record
     */
    public function objectRecord()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->hasOne('App\Models\Record', 'sid', 'object_sid')
            ->where('document_revision_id', $this->document_revision_id);
    }

    /**
     * @throws DataStructValidationException
     */
    public function validate()
    {

        if ($this->subjectRecord->record_type_sid != $this->linkType->domain_sid) {
            throw new DataStructValidationException("Validation fail in linktype.subject: incorrect type for this linktype (expects " . $this->linkType->bestTitle() . ")");
        }
        if ($this->objectRecord->record_type_sid != $this->linkType->range_sid) {
            throw new DataStructValidationException("Validation fail in linktype.object: incorrect type for this linktype (expects " . $this->linkType->bestTitle() . ")");
        }
    }
}



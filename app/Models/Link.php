<?php

namespace App\Models;

use App\Exceptions\MMValidationException;
use App\Http\TitleMaker;
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
     * @throws MMValidationException
     */
    public function validate()
    {
        $titleMaker = new TitleMaker();

        if (!isset($this->subjectRecord)) {
            throw new MMValidationException("Validation failed, no link subject");
        }
        if (!isset($this->objectRecord)) {
            throw new MMValidationException("Validation failed, no link object");
        }

        $forwardPeers = $this->subjectRecord->forwardLinks;
        $backPeers = $this->objectRecord->backLinks;
        $forwardCount = 1;
        $backCount = 1;

        /** @var Link $peer */
        foreach ($forwardPeers as $peer) {
            if ($peer->id == $this->id) {
                continue;
            }
            if ($peer->link_type_sid != $this->link_type_sid) {
                continue;
            }
            $forwardCount++;
            if ($peer->subject_sid == $this->subject_sid
                && $peer->object_sid == $this->object_sid
            ) {
                throw new MMValidationException("Link already exists");
            }
        }
        foreach ($backPeers as $peer) {
            if ($peer->id == $this->id) {
                continue;
            }
            if ($peer->link_type_sid != $this->link_type_sid) {
                continue;
            }
            $backCount++;
        }

        if ($forwardCount < $this->linkType->domain_min) {
            throw new MMValidationException("Too few links of this type on subject");
        }
        if ($backCount < $this->linkType->range_min) {
            throw new MMValidationException("Too few links of this type on object");
        }
        if (isset($this->linkType->domain_max) && $forwardCount > $this->linkType->domain_max) {
            throw new MMValidationException("Too many links of this type on subject");
        }
        if (isset($this->linkType->range_max) && $backCount > $this->linkType->range_max) {
            throw new MMValidationException("Too many links of this type on object $backCount/" . $this->linkType->domain_max);
        }

        if ($this->subjectRecord->record_type_sid != $this->linkType->domain_sid) {
            throw new MMValidationException("Subject of link should be a " . $titleMaker->title($this->linkType->domain));
        }
        if ($this->objectRecord->record_type_sid != $this->linkType->range_sid) {
            throw new MMValidationException("Target of link should be a " . $titleMaker->title($this->linkType->range));
        }
    }

}



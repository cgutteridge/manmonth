<?php

namespace App\Models;

use App\Exceptions\MMValidationException;
use App\Http\TitleMaker;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Link
 * @property int document_revision_id
 * @property int link_type_id
 * @property int subject_id
 * @property int object_id
 * @property Record subjectRecord
 * @property Record objectRecord
 * @property LinkType linkType
 * @package App\Models
 */
class Link extends DocumentPart
{
    /*************************************
     * RELATIONSHIPS
     *************************************/

    /**
     * @return BelongsTo
     */
    public function linkType()
    {
        return $this->belongsTo(LinkType::class);
    }

    /**
     * @return BelongsTo
     */
    public function subjectRecord()
    {
        return $this->belongsTo(Record::class, 'id', 'subject_id');
    }

    /**
     * @return BelongsTo
     */
    public function objectRecord()
    {
        return $this->belongsTo(Record::class, 'id', 'subject_id');
    }


    /*************************************
     * READ FUNCTIONS
     *************************************/

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
            if ($peer->link_type_id != $this->link_type_id) {
                continue;
            }
            $forwardCount++;
            if ($peer->subject_id == $this->subject_id
                && $peer->object_id == $this->object_id
            ) {
                throw new MMValidationException("Link already exists");
            }
        }
        foreach ($backPeers as $peer) {
            if ($peer->id == $this->id) {
                continue;
            }
            if ($peer->link_type_id != $this->link_type_id) {
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

        if ($this->subjectRecord->record_type_id != $this->linkType->domain_id) {
            throw new MMValidationException("Subject of link should be a " . $titleMaker->title($this->linkType->domain));
        }
        if ($this->objectRecord->record_type_id != $this->linkType->range_id) {
            throw new MMValidationException("Target of link should be a " . $titleMaker->title($this->linkType->range));
        }
    }


    /*************************************
     * ACTION FUNCTIONS
     *************************************/

    // none!
}



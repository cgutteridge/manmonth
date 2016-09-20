<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Link
 * @property int document_revision_id
 * @property int link_type_sid
 * @property int subject_sid
 * @property int object_sid
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
        return $this->hasOne( 'App\Models\LinkType', 'sid', 'link_type_sid' )
            ->where( 'document_revision_id', $this->document_revision_id );
    }

    /**
     * @return Record
     */
    public function subjectRecord()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->hasOne( 'App\Models\Record', 'sid', 'subject_sid' )
            ->where( 'document_revision_id', $this->document_revision_id );
    }

    /**
     * @return Record
     */
    public function objectRecord()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->hasOne( 'App\Models\Record', 'sid', 'object_sid' )
            ->where( 'document_revision_id', $this->document_revision_id );
    }

}



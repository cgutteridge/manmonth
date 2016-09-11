<?php

namespace App\Models;

/**
 * @property int document_revision_id
 * @property int link_type_sid
 * @property int subject_sid
 * @property int object_sid
 */
class Link extends DocumentPart
{
    /**
     * @return mixed
     */
    public function linkType()
    {
        return $this->hasOne( 'App\Models\LinkType', 'sid', 'link_type_sid' )->where( 'document_revision_id', $this->document_revision_id );
    }

    public function subject()
    {
        return $this->hasOne( 'App\Models\Record', 'sid', 'subject_sid' )->where( 'document_revision_id', $this->document_revision_id );
    }

    public function objectRecord()
    {
        return $this->hasOne( 'App\Models\Record', 'sid', 'object_sid' )->where( 'document_revision_id', $this->document_revision_id );
    }

}



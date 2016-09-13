<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @property int document_revision_id
 * @property int link_type_sid
 * @property int subject_sid
 * @property int object_sid
 */
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
     * @return Relation (LinkType)
     */
    public function linkType()
    {
        return $this->hasOne( 'App\Models\LinkType', 'sid', 'link_type_sid' )->where( 'document_revision_id', $this->document_revision_id );
    }

    /**
     * @return Relation (Record)
     */
    public function subject()
    {
        return $this->hasOne( 'App\Models\Record', 'sid', 'subject_sid' )->where( 'document_revision_id', $this->document_revision_id );
    }

    /**
     * @return Relation (Record)
     */
    public function objectRecord()
    {
        return $this->hasOne( 'App\Models\Record', 'sid', 'object_sid' )->where( 'document_revision_id', $this->document_revision_id );
    }

}



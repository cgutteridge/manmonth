<?php

namespace App;

use Exception;

class Link extends DocumentPart
{
    public function linkType()
    {
        return $this->hasOne( 'App\LinkType', 'sid', 'link_type_sid' )->where( 'document_revision_id', $this->document_revision_id );
    }

    public function subject()
    {
        return $this->hasOne( 'App\Record', 'sid', 'subject_sid' )->where( 'document_revision_id', $this->document_revision_id );
    }

    public function objectRecord()
    {
        return $this->hasOne( 'App\Record', 'sid', 'object_sid' )->where( 'document_revision_id', $this->document_revision_id );
    }

}



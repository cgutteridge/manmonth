<?php

namespace App\Models;

use Exception;


class Record extends DocumentPart
{
    public function recordType()
    {
        return $this->hasOne( 'App\Models\RecordType', 'sid', 'record_type_sid' )->where( 'document_revision_id', $this->document_revision_id );
    }

    public function forwardLinks()
    {
        return $this->hasMany( 'App\Models\Link', 'subject_sid', 'sid' )->where( 'document_revision_id', $this->document_revision_id );
    }
    public function backLinks()
    {
        return $this->hasMany( 'App\Models\Link', 'object_sid', 'sid' )->where( 'document_revision_id', $this->document_revision_id );
    }

    // return a text representation and all associated records 
    // following subject->object direction links only.
    // does not (yet) worry about loops.
    function dumpText($indent="") {
        $r = "";
        $r.= $indent."".$this->recordType->name."#".$this->sid." ".$this->data."\n";
        foreach( $this->forwardLinks as $link ) {
             $r.=$indent."  ->".$link->linkType->name."->\n";
             $object = $link->objectRecord;
             $r.=$link->objectRecord->dumpText( $indent."    " );
        }
        return $r;
    }
}



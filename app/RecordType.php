<?php

namespace App;

class RecordType extends DocumentPart
{
    public function newRecord()
    {
        $record = new Record();
        $record->documentRevision()->associate( $this->documentRevision );
        $record->record_type_sid = $this->sid;
        $record->save();
        return $record;
    }
}



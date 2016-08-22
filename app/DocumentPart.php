<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

abstract class DocumentPart extends Model
{
    public $timestamps = false;

    public function documentRevision()
    {
        return $this->belongsTo('App\DocumentRevision');
    }

    public function save(array $options = [])
    {
        $saved = parent::save($options);
        if( !$saved ) { return $saved; } // don't go on if it's already failed to save
        if( !$this->sid ) {
            $this->sid = $this->id;
            $saved &= parent::save($options);
        }
        return $saved;
    }
}



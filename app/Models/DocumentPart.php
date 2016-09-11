<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int sid
 * @property int id
 */
abstract class DocumentPart extends Model
{
    public $timestamps = false;

    /**
     * Relationship to the document revision this belongs to
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function documentRevision()
    {
        return $this->belongsTo('App\Models\DocumentRevision');
    }

    /**
     * @param array $options
     * @return bool
     */
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



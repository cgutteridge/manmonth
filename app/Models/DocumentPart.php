<?php

namespace App\Models;

/**
 * @property int sid
 * @property int id
 * @property array data
 * @property DocumentRevision $documentRevision;
 */
abstract class DocumentPart extends MMModel
{
    public $timestamps = false;

    protected $casts = [
        "data" => "array"
    ];
    protected $document_revision_id;


    /**
     * Relationship to the document revision this belongs to
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function documentRevision()
    {
        return $this->belongsTo('App\Models\DocumentRevision');
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        if ($key == 'documentRevision') {
            $relationCode = 'DocumentRevision#' . $this->document_revision_id;
            if (!array_key_exists($relationCode, MMModel::$cache)) {
                MMModel::$cache[$relationCode] = parent::getRelationValue($key);
            } else {
            }

            return MMModel::$cache[$relationCode];
        }

        return parent::__get($key);
    }

    /**
     * Overrides the default model save method. If the model doesn't have a sid
     * property set, it sets it to the id of the just saved record and resaves.
     * @param array $options
     * @return bool
     */
    public function save(array $options = [])
    {
        $saved = parent::save($options);
        if (!$saved) {
            return $saved;
        } // don't go on if it's already failed to save
        if (!$this->sid) {
            $this->sid = $this->id;
            $saved &= parent::save($options);
        }
        return $saved;
    }

    /**
     * Return true if create is blocked on this type.
     * @return bool
     */
    public function isProtected()
    {
        if (is_array($this->data) && !array_key_exists('protected', $this->data)) {
            return false;
        }
        return $this->data['protected'] == true;
    }
}



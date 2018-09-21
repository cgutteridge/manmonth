<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    /*************************************
     * RELATIONSHIPS
     *************************************/

    /**
     * Relationship to the document revision this belongs to
     * @return BelongsTo
     */
    public function documentRevision()
    {
        return $this->belongsTo(DocumentRevision::class);
    }

    /*************************************
     * READ FUNCTIONS
     *************************************/

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
            }

            return MMModel::$cache[$relationCode];
        }

        return parent::__get($key);
    }

    /*************************************
     * ACTION FUNCTIONS
     *************************************/

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


}



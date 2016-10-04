<?php

namespace App\Models;

use App\Exceptions\DataStructValidationException;
use Illuminate\Validation\Validator;

/**
 * @property int sid
 * @property int id
 */
abstract class DocumentPart extends MMModel
{
    public $timestamps = false;

    protected $casts = [
        "data" => "array"
    ];


    /**
     * Relationship to the document revision this belongs to
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function documentRevision()
    {
        return $this->belongsTo('App\Models\DocumentRevision');
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
     * @param Validator $validator
     * @return DataStructValidationException
     */
    protected function makeValidationException(Validator $validator)
    {
        $msg = "Validation failure.";
        $errors = $validator->errors();
        foreach ($errors->getMessages() as $fieldName => $list) {
            $msg .= " " . join(", ", $list);
            $msg .= " The $fieldName field had value " . json_encode($validator->getData()[$fieldName]) . ".";
        }
        return new DataStructValidationException($msg);
    }
}



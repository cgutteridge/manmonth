<?php
/**
 * Created by PhpStorm.
 * User: cjg
 * Date: 04/10/2016
 * Time: 22:51
 */

namespace App\Models;

use App\Exceptions\MMValidationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Validator;

/**
 * All out models always have an id field
 * @property int id
 */
abstract class MMModel extends Model
{
    static $cache = [];

    /**
     * Dynamically retrieve relations on the model.
     *
     * @param  string $key
     * @return mixed
     */
    public function getRelationValue($key)
    {
        if (!isset($this->id)) {
            # new object, probably shouldn't be asking for relations
            return parent::getRelationValue($key);
        }


        /* cache the same select from different instances of the same db object */
        $relationCode = get_class($this) . "#" . $this->id . "->" . $key;
        if (!array_key_exists($relationCode, MMModel::$cache)) {
            MMModel::$cache[$relationCode] = parent::getRelationValue($key);
        } else {
        }
        return MMModel::$cache[$relationCode];
    }


    /**
     * Helper function.
     *
     * @param Validator $validator
     * @return MMValidationException
     */
    protected
    function makeValidationException($validator)
    {
        $msg = "Validation failure.";
        $errors = $validator->errors();
        foreach ($errors->getMessages() as $fieldName => $list) {
            $msg .= " " . join(", ", $list);
            /* commented out as it's a bit buggy. Sometimes fieldName seems to start one too high.
            $data = $validator->getData();
            $value = $data;
            $codes = preg_split('/\./', $fieldName);
            while ($code = array_shift($codes)) {
                $value = $value[$code];
            }
            dump( $fieldName,$value);
            $msg .= " The field identified as $fieldName had the invalid value '" . $value . "''.";
            */
        }
        return new MMValidationException($msg);
    }

}


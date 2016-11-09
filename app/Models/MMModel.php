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
    /**
     * Helper function.
     *
     * @param Validator $validator
     * @return MMValidationException
     */
    protected function makeValidationException($validator)
    {
        $msg = "Validation failure.";
        $errors = $validator->errors();
        foreach ($errors->getMessages() as $fieldName => $list) {
            $msg .= " " . join(", ", $list);
            $msg .= " The $fieldName field had value " . json_encode($validator->getData()[$fieldName]) . ".";
        }
        return new MMValidationException($msg);
    }

}


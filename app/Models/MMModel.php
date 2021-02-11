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
 * @property DocumentRevision documentRevision
 */
abstract class MMModel extends Model
{
    /*************************************
     * READ FUNCTIONS
     *************************************/


    /*************************************
     * HELPER FUNCTIONS
     *************************************/

    /**
     * Helper function.
     *
     * @param Validator $validator
     * @return MMValidationException
     */
    protected function makeValidationException($validator)
    {
        $msg = "Validation failure. ";
        $errors = $validator->errors();
        foreach ($errors->getMessages() as $fieldName => $list) {
            $msg .= " $fieldName: " . join(". $fieldName: ", $list);
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


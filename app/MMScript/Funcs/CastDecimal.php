<?php

namespace App\MMScript\Funcs;

use App\Exceptions\CallException;
use App\MMScript\Values\BooleanValue;
use App\MMScript\Values\DecimalValue;
use App\MMScript\Values\IntegerValue;
use App\MMScript\Values\NullValue;
use App\MMScript\Values\StringValue;

// cast a value to integer
class CastDecimal
{
    var $name = "decimal";

    function type($types)
    {
        if (sizeof($types) != 1) {
            throw new CallException("decimal() expects exactly one perameter");
        }

        return "decimal";
    }

    function execute($params)
    {
        $param = $params[0];
        $outv = 0;
        $val = $param->value;
        if (is_a($param, DecimalValue::class) || is_a($param, IntegerValue::class)) {
            $outv = $val;
        } elseif (is_a($param, NullValue::class)) {
            $outv = 0;
        } elseif (is_a($param, BooleanValue::class) && $val == true) {
            $outv = 1;
        } elseif (is_a($param, BooleanValue::class) && $val == false) {
            $outv = 0;
        } elseif (is_a($param, StringValue::class)) {
            // use php's conversion
            $outv = floatval($val);
        }

        return new DecimalValue($outv);
    }
}

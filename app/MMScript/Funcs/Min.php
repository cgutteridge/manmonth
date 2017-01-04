<?php

namespace App\MMScript\Funcs;

use App\Exceptions\CallException;
use App\MMScript\Values\DecimalValue;
use App\MMScript\Values\IntegerValue;

class Min
{
    var $name = "min";

    // returns decimal, unless all the inputs are integers
    function type($types)
    {
        if (sizeof($types) == 0) {
            throw new CallException("min() expects at least one perameter");
        }
        $type = "integer";
        for ($i = 0; $i < count($types); $i++) {
            if ($types[$i] != "integer" && $types[$i] != "decimal") {
                throw new CallException("min() only operates on decimals and integers. Paramater " . ($i + 1) . " was passed a " . $types[$i]);
            }
            if ($types[$i] == "decimal") {
                $type = "decimal";
            }
        }
        return $type;
    }

    function execute($params)
    {
        $outv = $params[0]->value;
        $outtype = 'integer';
        foreach ($params as $param) {
            if ($param->value < $outv) {
                $outv = $param->value;
            }
            if (is_a($param, DecimalValue::class)) {
                $outtype = 'decimal';
            }
        }
        if ($outtype == 'integer') {
            return new IntegerValue($outv);
        }
        return new DecimalValue($outv);
    }
}

<?php

namespace App\MMScript\Funcs;

use App\Exceptions\CallException;
use App\MMScript\Values\IntegerValue;

class Floor
{
    var $name = "floor";

    function type($types)
    {
        if (sizeof($types) != 1) {
            throw new CallException("floor() expects exactly one perameter");
        }
        if ($types[0] != "integer" && $types[0] != "decimal") {
            throw new CallException("floor() only operates on decimals (actually also integers, but why would you do that?) but was passed a " . $types[0]);
        }
        return "integer";
    }

    function execute($params)
    {
        $outv = floor($params[0]->value);
        return new IntegerValue($outv);
    }
}

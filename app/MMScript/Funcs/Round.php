<?php

namespace App\MMScript\Funcs;

use App\Exceptions\CallException;
use App\MMScript\Values\IntegerValue;

class Round
{
    var $name = "round";

    function type($types)
    {
        if (sizeof($types) != 1) {
            throw new CallException("round() expects exactly one perameter");
        }
        if ($types[0] != "integer" && $types[0] != "decimal") {
            throw new CallException("round() only operates on decimals (actually also integers, but why would you do that?) but was passed a " . $types[0]);
        }
        return "integer";
    }

    function execute($params)
    {
        $outv = round($params[0]->value);
        return new IntegerValue($outv);
    }
}

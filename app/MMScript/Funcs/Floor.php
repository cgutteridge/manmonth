<?php

namespace App\MMScript\Funcs;

use App\Exceptions\CallException;

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
        dd("TODO");
        return 23;
    }
}

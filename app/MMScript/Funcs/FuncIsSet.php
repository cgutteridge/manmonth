<?php

namespace App\MMScript\Funcs;

use App\Exceptions\CallException;
use App\MMScript\Values\BooleanValue;

class FuncIsSet
{
    var $name = "isset";

    function type($types)
    {
        if (sizeof($types) != 1) {
            throw new CallException("isset() expects exactly one perameter");
        }

        return "boolean";
    }

    function execute($params)
    {
        $outv = isset($params[0]->value);
        return new BooleanValue($outv);
    }
}

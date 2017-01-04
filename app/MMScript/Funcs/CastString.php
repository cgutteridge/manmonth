<?php

namespace App\MMScript\Funcs;

use App\Exceptions\CallException;

// cast a value to string
class CastString
{
    var $name = "string";

    function type($types)
    {
        if (sizeof($types) != 1) {
            throw new CallException("string() expects exactly one perameter");
        }
        // accepts darn well anything!
        return "string";
    }

    function execute($params)
    {
        /** @var \App\MMScript\Values\Value $param */
        $param = $params[0];
        return $param->castString();
    }
}

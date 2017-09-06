<?php

namespace App\MMScript\Funcs;

use App\Exceptions\CallException;

// PHP gots upset if you try to call a class "if"!
class IfThenElse
{
    var $name = "if";

    // returns decimal, unless all the inputs are integers
    function type($types)
    {
        if (sizeof($types) != 3) {
            throw new CallException("if() expects exactly 3 parameters");
        }
        if ($types[0] != 'boolean') {
            throw new CallException("if() expects first parameter to be boolean");
        }
        if ($types[1] != $types[2]) {
            throw new CallException("if() expects second and third parameter to be exactly the same type. Got '" . $types[1] . "' and '" . $types[2] . "''");
        }
        return $types[1];
    }

    function execute($params)
    {
        if ($params[0]->value) {
            return $params[1];
        } else {
            return $params[2];
        }
    }
}

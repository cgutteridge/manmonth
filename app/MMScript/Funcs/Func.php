<?php

namespace App\MMScript\Funcs;

// a function just takes zero or more typed values and returns a typed value

// it can also be queried as to what type it would return given certain types
// this can throw an exception if the types don't make sense

// unlike Ops, functions are stateless monads. The "Op\Call" remembers the
// type for the specific context the function is called in.

abstract class Func
{
    public $name; // name of the function

    abstract function type($types);

    function recordType($types)
    {
        return null;
    } // only needed if type returned is a record type

    abstract function execute($params);
}

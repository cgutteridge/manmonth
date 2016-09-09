<?php

namespace App\MMScript\Funcs;

use App\Exceptions\CallException;

// cast a value to string
class String
{
    var $name = "string";

    function type( $types ) {
        if( sizeof( $types ) != 1 ) {
            throw new CallException( "string() expects exactly one perameter" );
        }
        // accepts darn well anything!
        return "string";
    }

    function execute( $params ) {
        dd( "TODO" );
        return 23;
    }
}

<?php

namespace App\MMScript\Func;

use App\CallException;

// cast a value to integer
class Decimal
{
    var $name = "decimal";

    function type( $types ) {
        if( sizeof( $types ) != 1 ) {
            throw new CallException( "decimal() expects exactly one perameter" );
        }
        if( $types[0]!="integer" && $types[0]!="decimal" ) {
            throw new CallException( "decimal() only operates on integers (actually also decimals, but why would you do that?) but was passed a ".$types[0] );
        }
        return "decimal";
    }

    function execute( $params ) {
        dd( "TODO" );
        return 23;
    }
}

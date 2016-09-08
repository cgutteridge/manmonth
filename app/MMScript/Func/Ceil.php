<?php

namespace App\MMScript\Func;

use App\Exceptions\CallException;

class Ceil
{
    var $name = "ceil";

    function type( $types ) {
        if( sizeof( $types ) != 1 ) {
            throw new CallException( "ceil() expects exactly one perameter" );
        }
        if( $types[0]!="integer" && $types[0]!="decimal" ) {
            throw new CallException( "ceil() only operates on decimals (actually also integers, but why would you do that?) but was passed a ".$types[0] );
        }
        return "integer";
    }

    function execute( $params ) {
        dd( "TODO" );
        return 23;
    }
}

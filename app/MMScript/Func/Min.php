<?php

namespace App\MMScript\Func;

use App\Exceptions\CallException;

class Min
{
    var $name = "min";

    // returns decimal, unless all the inputs are integers
    function type( $types ) {
        if( sizeof( $types ) == 0 ) {
            throw new CallException( "min() expects at least one perameter" );
        }
        $type = "integer";
        for( $i=0;$i<count($types);$i++ ) {
            if( $types[$i]!="integer" && $types[$i]!="decimal" ) {
                throw new CallException( "min() only operates on decimals and integers. Paramater ".($i+1)." was passed a ".$types[$i] );
            }
            if( $types[$i] == "decimal" ) { $type = "decimal"; } 
        }
        return $type;
    }

    function execute( $params ) {
        dd( "TODO" );
        return 23;
    }
}

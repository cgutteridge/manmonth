<?php

namespace App\MMScript\Ops;

use App\Exceptions\ScriptException;
use App\MMScript\Values\BooleanValue;

class NotOp extends UnaryOp
{
    function type() {
        if( @$this->type ) { return $this->type; }
        $lt = $this->param->type();

        if( ($lt == 'boolean' ) ) {
            $this->type = 'boolean';
            return $this->type;
        }

        throw new ScriptException( "Can't NOT $lt" );
    }

    function execute( $context )
    {
        return new BooleanValue( !$this->param->execute($context)->value );
    }
}

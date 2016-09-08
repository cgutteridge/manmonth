<?php

namespace App\MMScript;

use App\Exceptions\ScriptException;

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
}

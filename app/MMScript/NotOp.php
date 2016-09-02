<?php

namespace App\MMScript;

class NotOf extends UnaryOp
{
    function type() {
        if( @$this->type ) { return $this->type; }
        $lt = $this->param->type();

        if( ($lt == '#boolean' ) ) {
            $this->type = '#boolean';
            return $this->type;
        }

        throw new ScriptException( "Can't NOT $lt" );
    }
}

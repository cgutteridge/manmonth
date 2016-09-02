<?php

namespace App\MMScript;

class OrOp extends BinaryOp
{
    function type() {
        if( @$this->type ) { return $this->type; }
        $lt = $this->left->type();
        $rt = $this->right->type();

        if( ($lt == '#boolean'&&$rt == '#boolean' ) ) {
            $this->type = '#boolean';
            return $this->type;
        }

        throw new ScriptException( "Can't OR $lt and $rt" );
    }
}
}

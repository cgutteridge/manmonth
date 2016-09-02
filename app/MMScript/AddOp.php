<?php

namespace App\MMScript;

class AddOp extends BinaryOp
{
    function type() {
        if( @$this->type ) { return $this->type; }
        $lt = $this->left->type();
        $rt = $this->right->type();

        if( ($lt == '#integer'&&$rt == '#integer' ) ) {
            $this->type = '#integer';
            return $this->type;
        }

        if( ($lt == '#integer'||$lt == '#decimal' )  
         && ($rt == '#integer'||$rt == '#decimal' ) ) {
            $this->type = '#decimal';
            return $this->type;
        }

        throw new ScriptException( "Can't ADD $lt and $rt" );
    }
}

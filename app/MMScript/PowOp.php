<?php

namespace App\MMScript;

use App\ScriptException;

class PowOp extends BinaryOp
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

        throw new ScriptException( "Can't POW $lt and $rt" );
    }
}

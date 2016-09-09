<?php

namespace App\MMScript\Ops;

use App\Exceptions\ScriptException;

class CmpOp extends BinaryOp
{
    function type() {
        if( @$this->type ) { return $this->type; }
        $lt = $this->left->type();
        $rt = $this->right->type();
        if( $lt == 'string' && $rt == 'string' ) { 
            $this->type = 'boolean';
            return $this->type;
        }
        if( ($lt == 'integer'||$lt == 'decimal' )  
         && ($rt == 'integer'||$rt == 'decimal' ) ) {
            $this->type = 'boolean';
            return $this->type;
        }

        throw new ScriptException( "Can't compare $lt and $rt in a ".$this->opCode." operation" );
    }
}

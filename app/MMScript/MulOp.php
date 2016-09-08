<?php

namespace App\MMScript;

use App\Exceptions\ScriptException;

class MulOp extends BinaryOp
{
    function type() {
        if( @$this->type ) { return $this->type; }
        $lt = $this->left->type();
        $rt = $this->right->type();

        // multiply remains int but divide doesn't
        if( $this->opCode=="MUL" && $lt == 'integer'&&$rt == 'integer' ) {
            $this->type = 'integer'; 
            return $this->type;
        }

        if( ($lt == 'integer'||$lt == 'decimal' )  
         && ($rt == 'integer'||$rt == 'decimal' ) ) {
            $this->type = 'decimal';
            return $this->type;
        }

        throw new ScriptException( "Can't ".$this->opCode." $lt and $rt" );
    }
}

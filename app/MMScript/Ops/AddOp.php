<?php

namespace App\MMScript\Ops;

use App\Exceptions\ScriptException;

class AddOp extends BinaryOp
{
    function type() {
        if( @$this->type ) { return $this->type; }
        $lt = $this->left->type();
        $rt = $this->right->type();

        if( ($lt == 'integer'&&$rt == 'integer' ) ) {
            $this->type = 'integer';
            return $this->type;
        }

        if( ($lt == 'integer'||$lt == 'decimal' )  
         && ($rt == 'integer'||$rt == 'decimal' ) ) {
            $this->type = 'decimal';
            return $this->type;
        }

        # Adding a string to anything is OK as we can cast anything
        # into a string...
        if( $lt == 'string' || $rt == 'string' ) {
            $this->type = 'string';
            return $this->type;
        }

        throw new ScriptException( "Can't ADD $lt and $rt" );
    }
}

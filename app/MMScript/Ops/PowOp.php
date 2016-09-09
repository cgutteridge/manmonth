<?php

namespace App\MMScript\Ops;

use App\Exceptions\ScriptException;
use App\MMScript\Values\DecimalValue;
use App\MMScript\Values\IntegerValue;

class PowOp extends BinaryOp
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

        throw new ScriptException( "Can't POW $lt and $rt" );
    }

    function execute( $context )
    {
        $leftValue = $this->left->execute($context)->value;
        $rightValue = $this->right->execute($context)->value;
        $newValue = $leftValue ^ $rightValue;
        if( $this->type() == 'decimal') {
            return new DecimalValue( $newValue );
        }
        return new IntegerValue( $newValue );

    }

}

<?php

namespace App\MMScript\Ops;

use App\Exceptions\ScriptException;
use App\MMScript\Values\BooleanValue;

class OrOp extends BinaryOp
{
    function type() {
        if( @$this->type ) { return $this->type; }
        $lt = $this->left->type();
        $rt = $this->right->type();

        if( ($lt == 'boolean'&&$rt == 'boolean' ) ) {
            $this->type = 'boolean';
            return $this->type;
        }

        throw new ScriptException( "Can't OR $lt and $rt" );
    }

    function execute( $context )
    {
        $leftValue = $this->left->execute($context)->value;
        $rightValue = $this->right->execute($context)->value;

        return new BooleanValue( $leftValue || $rightValue );
    }
}

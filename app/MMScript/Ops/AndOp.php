<?php

namespace App\MMScript\Ops;

use App\Exceptions\ScriptException;
use App\MMScript\Values\BooleanValue;

/**
 * Class AndOp
 * @package App\MMScript\Ops
 */
class AndOp extends BinaryOp
{
    /**
     * @return string
     * @throws ScriptException
     */
    function type() {
        if( @$this->type ) { return $this->type; }
        $lt = $this->left->type();
        $rt = $this->right->type();

        if( ($lt == 'boolean'&&$rt == 'boolean' ) ) {
            $this->type = 'boolean';
            return $this->type;
        }

        throw new ScriptException( "Can't AND $lt and $rt" );
    }

    /**
     * @param array $context
     * @return BooleanValue
     */
    function execute($context )
    {
        $leftValue = $this->left->execute($context)->value;
        $rightValue = $this->right->execute($context)->value;

        return new BooleanValue( $leftValue && $rightValue );
    }
}

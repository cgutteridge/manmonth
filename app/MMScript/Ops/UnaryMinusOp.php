<?php

namespace App\MMScript\Ops;

use App\Exceptions\ScriptException;
use App\MMScript\Values\BooleanValue;

/**
 * Class UnaryMinusOp
 * @package App\MMScript\Ops
 */
class UnaryMinusOp extends UnaryOp
{
    /**
     * @return string
     * @throws ScriptException
     */
    function type()
    {
        if (@$this->type) {
            return $this->type;
        }
        $lt = $this->param->type();

        if (($lt == 'integer')) {
            $this->type = 'integer';
            return $this->type;
        }

        if (($lt == 'decimal')) {
            $this->type = 'decimal';
            return $this->type;
        }

        throw new ScriptException("Can't unary minus $lt");
    }

    /**
     * @param array $context
     * @return BooleanValue
     */
    function execute($context)
    {
        return new BooleanValue(-$this->param->execute($context)->value);
    }
}

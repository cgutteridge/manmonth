<?php

namespace App\MMScript\Ops;

use App\Exceptions\ScriptException;
use App\MMScript\Values\DecimalValue;
use App\MMScript\Values\IntegerValue;

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
     * @return DecimalValue|IntegerValue
     */
    function execute($context)
    {
        $value = -$this->param->execute($context)->value;
        if ($this->param->type() == 'integer') {
            return new IntegerValue($value);
        } else {
            return new DecimalValue($value);
        }
        // doesn't catch runtime type issues
    }
}

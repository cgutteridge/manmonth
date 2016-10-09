<?php

namespace App\MMScript\Ops;

use App\Exceptions\MMScriptRuntimeException;
use App\Exceptions\ScriptException;
use App\MMScript\Values\DecimalValue;
use App\MMScript\Values\IntegerValue;

/**
 * Class MulOp
 * @package App\MMScript\Ops
 */
class MulOp extends BinaryOp
{
    /**
     * @param array $context
     * @return DecimalValue|IntegerValue
     * @throws MMScriptRuntimeException
     */
    function execute($context)
    {
        $leftValue = $this->left->execute($context)->value;
        $rightValue = $this->right->execute($context)->value;

        if ($this->opCode == 'MUL') {
            $newValue = $leftValue * $rightValue;
        } else {
            try {
                $newValue = $leftValue / $rightValue;
            } catch (\ErrorException $e) {
                throw new MMScriptRuntimeException($e->getMessage());
            }
        }
        if ($this->type() == 'decimal') {
            return new DecimalValue($newValue);
        }
        return new IntegerValue($newValue);
    }

    /**
     * @return string
     * @throws ScriptException
     */
    function type()
    {
        if (@$this->type) {
            return $this->type;
        }
        $lt = $this->left->type();
        $rt = $this->right->type();

        // multiply remains int but divide doesn't
        if ($this->opCode == "MUL" && $lt == 'integer' && $rt == 'integer') {
            $this->type = 'integer';
            return $this->type;
        }

        if (($lt == 'integer' || $lt == 'decimal')
            && ($rt == 'integer' || $rt == 'decimal')
        ) {
            $this->type = 'decimal';
            return $this->type;
        }

        throw new ScriptException("Can't " . $this->opCode . " $lt and $rt");
    }
}

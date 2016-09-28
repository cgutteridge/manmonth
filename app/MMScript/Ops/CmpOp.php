<?php

namespace App\MMScript\Ops;

use App\Exceptions\ScriptException;
use App\Exceptions\MMScriptRuntimeException;
use App\MMScript\Values\BooleanValue;

class CmpOp extends BinaryOp
{
    function type()
    {
        if (@$this->type) {
            return $this->type;
        }
        $lt = $this->left->type();
        $rt = $this->right->type();
        if ($lt == 'string' && $rt == 'string') {
            $this->type = 'boolean';
            return $this->type;
        }
        if (($lt == 'integer' || $lt == 'decimal')
            && ($rt == 'integer' || $rt == 'decimal')
        ) {
            $this->type = 'boolean';
            return $this->type;
        }

        throw new ScriptException("Can't compare $lt and $rt in a " . $this->opCode . " operation");
    }

    function execute($context)
    {
        # "EQ","NEQ","LEQ","GEQ","LT","GT"
        $leftValue = $this->left->execute($context)->value;
        $rightValue = $this->right->execute($context)->value;

        if ($this->opCode == 'EQ') {
            return new BooleanValue($leftValue == $rightValue);
        } elseif ($this->opCode == 'GT') {
            return new BooleanValue($leftValue > $rightValue);
        } elseif ($this->opCode == 'LT') {
            return new BooleanValue($leftValue < $rightValue);
        } elseif ($this->opCode == 'NEQ') {
            return new BooleanValue($leftValue != $rightValue);
        } elseif ($this->opCode == 'GEQ') {
            return new BooleanValue($leftValue >= $rightValue);
        } elseif ($this->opCode == 'LEQ') {
            return new BooleanValue($leftValue <= $rightValue);
        } else {
            throw new MMScriptRuntimeException("bad comparison op code: " . $this->opCode);
        }
    }
}


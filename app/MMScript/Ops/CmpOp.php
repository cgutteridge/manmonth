<?php

namespace App\MMScript\Ops;

use App\Exceptions\MMScriptRuntimeException;
use App\Exceptions\ScriptException;
use App\MMScript\Values\BooleanValue;
use App\MMScript\Values\NullValue;

class CmpOp extends BinaryOp
{
    function type()
    {
        if (@$this->type) {
            return $this->type;
        }
        $lt = $this->left->type();
        $rt = $this->right->type();

        // this might be better handled by making a isString()
        // method on the ops, but for now treat option fields as
        // strings
        if ($lt == 'option') {
            $lt = 'string';
        }
        if ($rt == 'option') {
            $rt = 'string';
        }

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

        if (($this->opCode == 'EQ' || $this->opCode == "NEQ") &&
            ($lt == 'null' || $rt == 'null')
        ) {
            $this->type = 'boolean';
            return $this->type;
        }

        throw new ScriptException("Can't compare $lt and $rt in a " . $this->opCode . " operation");
    }

    function execute($context)
    {
        # "EQ","NEQ","LEQ","GEQ","LT","GT"
        $left = $this->left->execute($context);
        $right = $this->right->execute($context);

        $leftValue = $left->value;
        $rightValue = $right->value;

        if (is_a($left, NullValue::class)) {
            $leftValue = $right->myNull();
        }

        if (is_a($right, NullValue::class)) {
            $rightValue = $left->myNull();
        }

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


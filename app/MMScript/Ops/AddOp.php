<?php

namespace App\MMScript\Ops;

use App\Exceptions\MMScriptRuntimeException;
use App\Exceptions\ScriptException;
use App\MMScript\Values\DecimalValue;
use App\MMScript\Values\StringValue;
use App\MMScript\Values\IntegerValue;

/*
 * @property Record left
 * @property Name right
 */

class AddOp extends BinaryOp
{
    /**
     * @return string
     * @throws ScriptException
     */
    function type()
    {
        if (isset($this->type)) {
            return $this->type;
        }
        $lt = $this->left->type();
        $rt = $this->right->type();

        if (($lt == 'integer' && $rt == 'integer')) {
            $this->type = 'integer';
            return $this->type;
        }

        if (($lt == 'integer' || $lt == 'decimal')
            && ($rt == 'integer' || $rt == 'decimal')
        ) {
            $this->type = 'decimal';
            return $this->type;
        }

        # Adding a string to anything is OK as we can cast anything
        # into a string. Can't minus a string...
        if (($lt == 'string' || $rt == 'string') && $this->opCode == "PLUS") {
            $this->type = 'string';
            return $this->type;
        }

        throw new ScriptException("Can't " . $this->opCode . " $lt and $rt");
    }

    function execute($context)
    {
        $leftValue = $this->left->execute($context)->value;
        $rightValue = $this->right->execute($context)->value;
        try {
            $type = $this->type();
        } catch (ScriptException $e) {
            throw new MMScriptRuntimeException($e->getMessage());
        }
        if ($type == "string") {
            return new StringValue("$leftValue$rightValue");
        }
        if ($this->opCode == 'MIN') {
            $rightValue = -$rightValue;
        }
        if ($type == 'integer') {
            return new IntegerValue($leftValue + $rightValue);
        }
        return new DecimalValue($leftValue + $rightValue);
    }
}

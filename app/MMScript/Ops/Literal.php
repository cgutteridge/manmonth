<?php

namespace App\MMScript\Ops;

use App\Exceptions\MMScriptRuntimeException;
use App\Exceptions\ScriptException;
use App\MMScript\Values\BooleanValue;
use App\MMScript\Values\DecimalValue;
use App\MMScript\Values\IntegerValue;
use App\MMScript\Values\StringValue;

/**
 * Class Literal
 * @package App\MMScript\Ops
 */
class Literal extends Op
{
    /**
     * @return string
     */
    public function type()
    {
        if (@$this->type) {
            return $this->type;
        }
        $map = ['STR' => 'string', 'DEC' => 'decimal', 'INT' => 'integer', 'BOOL' => 'boolean'];
        if (!array_key_exists($this->opCode, $map)) {
            throw new ScriptException("Unknown literal type: " . $this->opCode);
        }
        $this->type = $map[$this->opCode];
        return $this->type;
    }

    /**
     * @param $context
     * @return BooleanValue|DecimalValue|IntegerValue|StringValue
     * @throws MMScriptRuntimeException
     */
    function execute($context)
    {
        switch ($this->type()) {
            case "integer":
                return new IntegerValue($this->value);
            case "decimal":
                return new DecimalValue($this->value);
            case "boolean":
                return new BooleanValue($this->value);
            case "string":
                return new StringValue($this->value);
        }
        throw new MMScriptRuntimeException("Literal of literally unknown type: " . $this->type());
    }

    /**
     * @param string $prefix
     * @return string
     */
    public function treeText($prefix = "")
    {
        $r = $prefix . get_class($this) . " :: " . $this->opCode . " -> " . $this->value . " [" . @$this->type() . "]\n";
        return $r;
    }
}

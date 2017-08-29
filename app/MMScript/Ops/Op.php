<?php

namespace App\MMScript\Ops;

use App\MMScript;
use App\MMScript\Values\Value;
use App\Models\RecordType;

/**
 * Class Op
 * @package App\MMScript\Ops
 */
abstract class Op
{
    var $offset;
    var $opCode;
    var $value;
    var $script;
    protected $type;

    /**
     * Op constructor.
     * @param MMScript $script
     * @param array $token
     */
    public function __construct(MMScript $script, $token)
    {
        $this->script = $script;
        $this->offset = $token[0];
        $this->opCode = $token[1];
        if (isset($token[2])) {
            $this->value = $token[2];
        }
    }

    /**
     * @return string
     */
    public abstract function type();

    /**
     * @param string $prefix
     * @return string
     */
    public function treeText($prefix = "")
    {
        $r = $prefix . get_class($this) . " :: " . $this->opCode . " -> " . @$this->value . " [" . @$this->type() . "]\n";
        return $r;
    }

    // might be needed if a function returns type 'record' later?

    /**
     * @return null|RecordType
     */
    function recordType()
    {
        return null;
    }

    /**
     * @param $context
     * @return Value
     */
    public abstract function execute($context);
}

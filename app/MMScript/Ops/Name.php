<?php

namespace App\MMScript\Ops;

use App\MMScript\Values\NameValue;

/**
 * Class Name
 * @package App\MMScript\Ops
 */
class Name extends Op
{
    /**
     * @var string
     */
    var $type = "name";

    /**
     * @return string
     */
    public function type()
    {
        return "name";
    }

    /**
     * @param array $context
     * @return NameValue
     */
    function execute($context)
    {
        return new NameValue($this->value);
    }

    /**
     * @param string $prefix
     * @return string
     */
    public function treeText($prefix = "")
    {
        $r = $prefix . $this->opCode . " -> " . $this->value . " [" . @$this->type() . "]\n";
        return $r;
    }
}

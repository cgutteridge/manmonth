<?php

namespace App\MMScript\Ops;

use App\Exceptions\MMScriptRuntimeException;
use App\MMScript;
use App\MMScript\Values\NullValue;

# list of expressions
/**
 * @property Op[] list
 */
class ExpList extends Op
{
    /**
     * UnaryOp constructor.
     * @param MMScript $script
     * @param array $token
     * @param Op[] $list
     */
    public function __construct($script, $token, $list)
    {
        $this->list = $list;
        parent::__construct($script, $token);
    }

    /**
     * @param string $prefix
     * @return string
     */
    public function treeText($prefix = "")
    {
        $r = $prefix . get_class($this) . " [" . @$this->type() . "]\n";
        /** @var Op $item */
        foreach ($this->list as $item) {
            $r .= $item->treeText($prefix . "  ");
        }
        return $r;
    }

    # hard wired type
    /**
     * @var string
     */
    var $type = "list";

    /**
     * @return string
     */
    public function type()
    {
        return "list";
    }

    /**
     * @param array $context
     * @return NullValue
     * @throws MMScriptRuntimeException
     */
    function execute($context)
    {
        throw new MMScriptRuntimeException("ExpList should not be executed");
        // maybe later we can do something smart with lists as their own type
    }

}

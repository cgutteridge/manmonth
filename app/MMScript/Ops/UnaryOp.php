<?php

namespace App\MMScript\Ops;

use App\MMScript;

/**
 * Class UnaryOp
 * @package App\MMScript\Ops
 */
abstract class UnaryOp extends Op
{
    /**
     * @var Op
     */
    var $param;

    /**
     * UnaryOp constructor.
     * @param MMScript $script
     * @param array $token
     * @param Op $param
     */
    public function __construct($script, $token, $param)
    {
        $this->param = $param;
        parent::__construct($script, $token);
    }

    /**
     * @param string $prefix
     * @return string
     */
    public function treeText($prefix = "")
    {
        $r = $prefix . get_class($this) . " :: " . $this->opCode . " [" . @$this->type() . "]\n";
        $r .= $this->param->treeText($prefix . "  ");
        return $r;
    }
}

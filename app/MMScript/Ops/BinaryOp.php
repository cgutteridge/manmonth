<?php

namespace App\MMScript\Ops;

/**
 * @property Op left
 * @property Op right
 */
abstract class BinaryOp extends Op
{
    public $left;
    public $right;

    public function __construct($script, $token, $left, $right)
    {
        $this->left = $left;
        $this->right = $right;
        parent::__construct($script, $token);
    }

    public function treeText($prefix = "")
    {
        $r = $prefix . get_class($this) . " :: " . $this->opCode . " [" . @$this->type() . "]\n" . $this->left->treeText($prefix . "  ") . $this->right->treeText($prefix . "  ");
        return $r;
    }
}


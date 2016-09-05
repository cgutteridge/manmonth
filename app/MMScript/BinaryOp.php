<?php

namespace App\MMScript;

use App\ScriptException;

// op with left & right param
abstract class BinaryOp extends Op
{
    var $left;
    var $right;
    public function __construct( $script, $op, $left, $right ) {
        $this->left = $left;
        $this->right = $right;
        parent::__construct($script,$op);
    }

    public function treeText( $prefix = "" ) {
        $r = $prefix.get_class( $this )." :: ".$this->opCode." [".@$this->type."]\n".$this->left->treeText($prefix."  ").$this->right->treeText($prefix."  ");
        return $r;
    }
}


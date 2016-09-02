<?php

namespace App\MMScript;

// op with left & right param
abstract class BinaryOp extends Op
{
    var $left;
    var $right;
    public function __construct( $op, $left, $right ) {
        $this->left = $left;
        $this->right = $right;
        parent::__construct($op);
    }

    public function treeText( $prefix = "" ) {
        $r = $prefix.get_class( $this )." :: ".$this->op[1]."\n".$this->left->treeText($prefix."  ").$this->right->treeText($prefix."  ");
        return $r;
    }
}


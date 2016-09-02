<?php

namespace App\MMScript;

abstract class UnaryOp extends Op
{
    var $param;
    public function __construct( $script, $op, $param ) {
        $this->param = $param;
        parent::__construct($script, $op);
    }
    public function treeText( $prefix = "" ) {
        $r = $prefix.get_class( $this )." :: ".$this->opCode." [".@$this->type."]\n".$param->treeText($prefix."  ");
        return $r;
    }
}

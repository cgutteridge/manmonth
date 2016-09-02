<?php

namespace App\MMScript;

abstract class UnaryOp extends Op
{
    var $param;
    public function __construct( $op, $param ) {
        $this->param = $param;
        parent::__construct($op);
    }
    public function treeText( $prefix = "" ) {
        $r = $prefix.get_class( $this )." :: ".$op[1]." [".@$this->type."]\n".$param->treeText($prefix."  ");
        return $r;
    }
}

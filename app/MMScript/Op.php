<?php

namespace App\MMScript;

abstract class Op 
{
    var $op;
    var $type;
    public function __construct( $op ) {
        $this->op = $op;
    }
    public function treeText( $prefix = "" ) {
        $r = $prefix.get_class( $this )." :: ".$this->op[1]." -> ".@$this->op[2]." [".@$this->type."]\n";
        return $r;
    }
}

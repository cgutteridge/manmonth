<?php

namespace App\MMScript;

abstract class Op 
{
    var $offset;
    var $opCode;
    var $value;
    var $script;
    var $type;

    public function __construct( $script,$op ) {
        $this->script = $script;
        $this->offset = $op[0];
        $this->opCode = $op[1];
        $this->value = @$op[2];
    }

    public function treeText( $prefix = "" ) {
        $r = $prefix.get_class( $this )." :: ".$this->opCode." -> ".@$this->value." [".@$this->type."]\n";
        return $r;
    } 
}

<?php

namespace App\MMScript;

class FieldOf extends UnaryOp
{
    public function treeText( $prefix = "" ) {
        $r = $prefix.get_class( $this )." :: ".$this->op[1]."\n".$this->param->treeText($prefix."  ").$prefix."  ".$this->op[1]." - ".$this->op[2]."\n";
        return $r;
    }
}

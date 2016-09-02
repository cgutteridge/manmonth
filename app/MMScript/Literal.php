<?php

namespace App\MMScript;

class Literal extends Op
{
    public function treeText( $prefix = "" ) {
        $r = $prefix.get_class( $this )." :: ".$this->op[1]." -> ".$this->op[2]."\n";
        return $r;
    }
}

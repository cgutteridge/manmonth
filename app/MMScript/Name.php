<?php

namespace App\MMScript;

class Name extends Op
{
    public function treeText( $prefix = "" ) {
        $r = $prefix.$this->op[1]." -> ".$this->op[2]." [".@$this->type."]\n";
        return $r;
    }
}

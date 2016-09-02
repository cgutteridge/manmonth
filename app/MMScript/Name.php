<?php

namespace App\MMScript;

class Name extends Op
{
    public function treeText( $prefix = "" ) {
        $r = $prefix.$this->opCode." -> ".$this->opValue." [".@$this->type."]\n";
        return $r;
    }

    public function type() { return "#name"; }
}

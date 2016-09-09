<?php

namespace App\MMScript\Ops;

use App\Exceptions\ScriptException;

class Name extends Op
{
    public function treeText( $prefix = "" ) {
        $r = $prefix.$this->opCode." -> ".$this->value." [".@$this->type()."]\n";
        return $r;
    }

    # hard wired type
    var $type = "name";
    public function type() { return "name"; }
}

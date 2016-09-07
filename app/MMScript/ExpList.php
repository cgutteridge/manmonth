<?php

namespace App\MMScript;

use App\ScriptException;

# list of expressions
class ExpList extends UnaryOp
{
    public function treeText( $prefix = "" ) {
        $r = $prefix.get_class( $this )." [".@$this->type()."]\n";
        foreach( $this->param as $item ) {
            $r.= $item->treeText($prefix."  ");
        }
        return $r;
    }

    # hard wired type
    var $type = "list";
    public function type() { return "list"; }
}

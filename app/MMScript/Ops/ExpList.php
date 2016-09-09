<?php

namespace App\MMScript\Ops;

use App\Exceptions\ScriptException;
use App\Exceptions\MMScriptRuntimeException;

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

    function execute( $context ) {
        throw new MMScriptRuntimeException( "ExpList should not be executed");
        // maybe later we can do something smart with lists as their own type
    }

}

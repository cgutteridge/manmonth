<?php

namespace App\MMScript\Ops;

use App\MMScript\Values\NameValue;

class Name extends Op
{
    public function treeText( $prefix = "" ) {
        $r = $prefix.$this->opCode." -> ".$this->value." [".@$this->type()."]\n";
        return $r;
    }

    # hard wired type
    var $type = "name";
    public function type() { return "name"; }

    function execute( $context )
    {
        return new NameValue( $this->value );
    }
}

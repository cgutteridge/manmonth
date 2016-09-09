<?php

namespace App\MMScript\Ops;

use App\Exceptions\MMScriptRuntimeException;
use App\MMScript\Values\BooleanValue;
use App\MMScript\Values\DecimalValue;
use App\MMScript\Values\IntegerValue;
use App\MMScript\Values\StringValue;

class Literal extends Op
{
    public function treeText( $prefix = "" ) {
        $r = $prefix.get_class( $this )." :: ".$this->opCode." -> ".$this->value." [".@$this->type()."]\n";
        return $r;
    }

    public function type() {
        if( @$this->type ) { return $this->type; }
        $map = ['STR'=>'string','DEC'=>'decimal','INT'=>'integer','BOOL'=>'boolean'];
        $this->type = $map[ $this->opCode ];
        return $this->type;
    }

    function execute($context)
    {
        switch( $this->type() ) {
            case "integer":
                return new IntegerValue($this->value);
            case "decimal":
                return new DecimalValue($this->value);
            case "boolean":
                return new BooleanValue($this->value);
            case "string":
                return new StringValue($this->value);
        }
        throw new MMScriptRuntimeException( "Literal of literally unknown type: ".$this->type());
    }
}

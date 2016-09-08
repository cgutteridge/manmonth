<?php

namespace App\MMScript;

use App\Exceptions\ScriptException;

class Record extends Op
{
    function type() {
        if( @$this->type ) { return $this->type; }
        $this->type = "record";
        return $this->type;
    }

    var $recordType;
    function recordType() {
        if( @$this->recordType ) { return $this->recordType; }
        if( !@$this->script->context[ $this->value ] ) {
            throw new ScriptException( "Can't see record type reference '".$this->value."' in script context. Valid terms are ".join( ", ", array_keys( $this->script->context ) )."." );
        }
        $this->recordType = $this->script->context[ $this->value ];
        $this->type = $this->recordType->name;
        return $this->recordType;
    }
}

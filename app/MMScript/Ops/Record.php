<?php

namespace App\MMScript\Ops;

use App\Exceptions\ScriptException;
use App\Exceptions\MMScriptRuntimeException;
use App\MMScript\Values\RecordValue;



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
        return $this->recordType;
    }

    function execute( $context )
    {
        if( !isset($context[$this->value])) {
            throw new MMScriptRuntimeException( "Context does not contain ".$this->value.". Context has: [".join( ", ", array_keys( $context )));
        }
        return new RecordValue( $context[ $this->value ] );
    }
}

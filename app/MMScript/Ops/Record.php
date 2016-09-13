<?php

namespace App\MMScript\Ops;

use App\Exceptions\ScriptException;
use App\Exceptions\MMScriptRuntimeException;
use App\MMScript\Values\RecordValue;
use App\Models\RecordType;


/**
 * Class Record
 * @package App\MMScript\Ops
 */
class Record extends Op
{
    private $recordType;

    /**
     * @return string
     */
    function type() {
        if( @$this->type ) { return $this->type; }
        $this->type = "record";
        return $this->type;
    }

    /**
     * @return RecordType
     * @throws ScriptException
     */
    function recordType() {
        if( @$this->recordType ) { return $this->recordType; }
        if( !@$this->script->context[ $this->value ] ) {
            throw new ScriptException( "Reference to non-existant item '".$this->value."' in the context of this script. Valid names are: ".join( ", ", array_keys( $this->script->context ) ).". ".$this->script->text );
        }
        $this->recordType = $this->script->context[ $this->value ];
        return $this->recordType;
    }

    /**
     * @param $context
     * @return RecordValue
     * @throws MMScriptRuntimeException
     */
    function execute($context )
    {
        if( !isset($context[$this->value])) {
            throw new MMScriptRuntimeException( "Context does not contain ".$this->value.". Context has: [".join( ", ", array_keys( $context )));
        }
        return new RecordValue( $context[ $this->value ] );
    }
}

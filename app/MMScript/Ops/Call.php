<?php

namespace App\MMScript\Ops;

use App\Exceptions\ScriptException;

// This provides an extensible functions feature
// possibly normal Op functions should all be calls, or vice versa?
class Call extends BinaryOp
{
    // there's probably a cleverer laravel way of doing this...
    static protected $funcs = [
          Func\Round::class,
          Func\Floor::class,
          Func\Ceil::class,
          Func\Decimal::class,
          Func\String::class,
          Func\Min::class,
          Func\Max::class,
    ];

    static protected $funcCache;
    public static function funcs() {
        if( self::$funcCache ) { return self::$funcCache; }
        self::$funcCache = [];
        foreach( self::$funcs as $class ) {
            $func = new $class();
            self::$funcCache[$func->name] = $func;
        }
        return self::$funcCache; 
    }
    public static function func( $funcName ) {
        $funcs = self::funcs();
        return $funcs[$funcName];
    }    

    function type() {
        if( @$this->type ) { return $this->type; }

        $funcName = $this->left->value;
        $func = self::func( $funcName );
        if( !$func ) {
            throw new ScriptException( "Unknown function call: $funcName" );
        }
        if( $this->right->type() != "list" ) {
            throw new ScriptException( "$funcName was not passed a list but rather a ".$this->right->type() );
        }
     
        $this->type = $func->type( $this->paramTypes() );   
        return $this->type;
    }
    function paramTypes() {
        $types = [];
        foreach( $this->right->param as $op ) {
            $types []= $op->type();
        }
        return $types;
    }

    // might be needed if a function returns type 'record' later?
    function recordType() {
        if( !$this->type() == "record" ) { return null; }
        return $func->recordType( $this->paramTypes() );
    }
}

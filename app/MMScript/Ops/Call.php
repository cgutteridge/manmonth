<?php

namespace App\MMScript\Ops;

use App\Exceptions\CallException;
use App\Exceptions\ScriptException;
use App\MMScript\Funcs\CastDecimal;
use App\MMScript\Funcs\CastString;
use App\MMScript\Funcs\Ceil;
use App\MMScript\Funcs\Floor;
use App\MMScript\Funcs\Func;
use App\MMScript\Funcs\FuncIsSet;
use App\MMScript\Funcs\IfThenElse;
use App\MMScript\Funcs\Max;
use App\MMScript\Funcs\Min;
use App\MMScript\Funcs\Round;
use App\MMScript\Values\Value;
use App\Models\RecordType;

// This provides an extensible functions feature
// possibly normal Op functions should all be calls, or vice versa?
/**
 * Class Call
 * @package App\MMScript\Ops
 * @property Name left
 * @property ExpList right
 */
class Call extends BinaryOp
{
    // there's probably a cleverer laravel way of doing this...
    /**
     * @var array
     */
    static protected $funcs = [
        Round::class,
        Floor::class,
        Ceil::class,
        CastDecimal::class,
        CastString::class,
        Min::class,
        Max::class,
        IfThenElse::class,
        FuncIsSet::class,
    ];

    /**
     * @var Func[]
     */
    static protected $funcCache;
    /**
     * @var Func
     */
    protected $func;

    /**
     * @return null|RecordType
     */
    function recordType()
    {
        if (!$this->type() == "record") {
            return null;
        }
        $func = $this->func();
        return $func->recordType($this->paramTypes());
    }

    /**
     * @return string
     * @throws ScriptException
     */
    function type()
    {
        if (@$this->type) {
            return $this->type;
        }

        $func = $this->func();
        if ($this->right->type() != "list") {
            throw new ScriptException("Function " . $func->name . " was not passed a list but rather a " . $this->right->type());
        }

        $this->type = $func->type($this->paramTypes());
        return $this->type;
    }

    /**
     * @return Func
     * @throws ScriptException
     */
    function func()
    {
        if (@$this->func) {
            return $this->func;
        }

        $funcName = $this->left->value;
        $this->func = self::funcFactory($funcName);
        if (!$this->func) {
            throw new ScriptException("Unknown function call: $funcName");
        }
        return $this->func;
    }

    /**
     * @param $funcName
     * @return Func
     * @throws CallException
     */
    public static function funcFactory($funcName)
    {
        $funcs = self::funcs();
        if (!array_key_exists($funcName, $funcs)) {
            throw new CallException("Unknown function name '$funcName'.");
        }
        return $funcs[$funcName];
    }

    /**
     * @return Func[]
     */
    public static function funcs()
    {
        if (self::$funcCache) {
            return self::$funcCache;
        }
        self::$funcCache = [];
        foreach (self::$funcs as $class) {
            $func = new $class();
            self::$funcCache[$func->name] = $func;
        }
        return self::$funcCache;
    }

    // might be needed if a function returns type 'record' later?

    /**
     * @return string[]
     */
    function paramTypes()
    {
        $types = [];
        foreach ($this->right->list as $op) {
            $types [] = $op->type();
        }
        return $types;
    }

    /**
     * @param $context
     * @return Value
     */
    function execute($context)
    {
        $params = [];
        foreach ($this->right->list as $op) {
            $params [] = $op->execute($context);
        }

        return $this->func()->execute($params);
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: cjg
 * Date: 09/09/2016
 * Time: 11:33
 */

namespace App\MMScript;

use App\Exceptions\ParseException;
use App\MMScript;

/**
 * @property MMScript $script
 */
class Compiler
{
    protected $tokens; // a list of all the tokens as [ charoffset, codestring, value]
    protected $offset; // the offset through the tokens while compiling

    public function __construct($script)
    {
        $this->script = $script;
    }

    public function compile()
    {
        $tokeniser = new Tokeniser();
        $this->tokens = $tokeniser->tokenise($this->script->text);
        $this->offset = 0;
        $expression = $this->compileExpression();
        if ($this->moreTokens()) {
            throw new ParseException("Unexpected additional symbols after end of expression", $this->script->text, $this->token()[0]);
        }
        return $expression;
    }

    public function compileExpression()
    {
        return $this->compileOr();
    }

    public function compileOr()
    {
        $left = $this->compileAnd();
        if ($this->moreTokens() && $this->tokenIs("OR")) {
            $op = $this->token();
            $this->offset++;
            $right = $this->compileOr();
            return new Ops\OrOp($this->script, $op, $left, $right);
        }
        return $left;
    }

    public function compileAnd()
    {
        $left = $this->compileCmp();
        if ($this->moreTokens() && $this->tokenIs("AND")) {
            $op = $this->token();
            $this->offset++;
            $right = $this->compileAnd();
            return new Ops\AndOp($this->script, $op, $left, $right);
        }
        return $left;
    }

    public function compileCmp()
    {
        $left = $this->compileNot();
        if ($this->moreTokens() && $this->tokenIs(["EQ", "NEQ", "LEQ", "GEQ", "LT", "GT"])) {
            $op = $this->token();
            $this->offset++;
            $right = $this->compileCmp();
            return new Ops\CmpOp($this->script, $op, $left, $right);
        }
        return $left;
    }


    # <EXP>   = <OROP>

    public function compileNot()
    {
        if ($this->tokenIs("NOT")) {
            $op = $this->token();
            $this->offset++;
            $right = $this->compileNot();
            return new Ops\NotOp($this->script, $op, $right);
        }
        return $this->compileUnaryMinus();
    }

    # <OROP>  = <ANDOP> [ "|" + <OROP> ]

    protected function tokenIs($ids)
    {
        if (!$this->moreTokens()) {
            return false;
        }
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $code = $this->tokens[$this->offset][1];

        foreach ($ids as $id) {
            if ($id == $code) {
                return true;
            }
        }
        return false;
    }

    # <ANDOP> = <CMPOP> [ "&" + <ANDOP> ]

    protected function moreTokens()
    {
        return $this->offset < sizeof($this->tokens);
    }

    # <CMPOP> = <NOTOP> [ ( "=" | "<>" | ">=" | "<=" | ">" | "<" ) <CMPOP> ]

    protected function token()
    {
        if (!$this->moreTokens()) {
            return [strlen($this->script->text), "END"];
        }
        return $this->tokens[$this->offset];
    }

    # <NOTOP> = "!" <NOTOP> | <UMINUS>

    public function compileUnaryMinus()
    {
        if ($this->tokenIs("MIN")) {
            $op = $this->token();
            $this->offset++;
            $right = $this->compileUnaryMinus();
            return new Ops\UnaryMinusOp($this->script, $op, $right);
        }
        return $this->compileAdd();
    }

    # <UMINUS> = "-" <NOTOP> | <ADDOP>

    public function compileAdd()
    {
        $left = $this->compileMul();
        if ($this->moreTokens() && $this->tokenIs(["PLUS", "MIN"])) {
            $op = $this->token();
            $this->offset++;
            $right = $this->compileAdd();
            return new Ops\AddOp($this->script, $op, $left, $right);
        }
        return $left;
    }

    # <ADDOP> = <MULOP> [ ("+"|"-") <ADDOP> ]

    public function compileMul()
    {
        $left = $this->compilePow();
        while ($this->moreTokens() && $this->tokenIs(["MUL", "DIV"])) {
            $op = $this->token();
            $this->offset++;
            $right = $this->compilePow();
            $left = new Ops\MulOp($this->script, $op, $left, $right);
        }
        return $left;
    }

    # <MULOP> = <POWOP> [ ("*"|"/") <MULOP> ]

    public function compilePow()
    {
        $left = $this->compileBracket();
        if ($this->moreTokens() && $this->tokenIs(["POW"])) {
            $op = $this->token();
            $this->offset++;
            $right = $this->compilePow();
            return new Ops\PowOp($this->script, $op, $left, $right);
        }
        return $left;
    }

    # <POWOP> = <BRAOP> [ "^" <POPOP> ]

    public function compileBracket()
    {
        if ($this->tokenIs(["OBR"])) {
            $this->offset++; // consume open bracket
            $exp = $this->compileExpression();
            if (!$this->tokenIs(["CBR"])) {
                throw new ParseException("Expected close bracket", $this->script->text, $this->token()[0]);
            }
            $this->offset++; // consume close bracket
            return $exp;
        }

        return $this->compileValue();
    }

    # <BRAOP> = "(" <EXP> ")" | <VALUE>

    public function compileValue()
    {
        # <LITERAL> = "true" | "false" | [1-9][0-9]* | [0-9]+ "." [0-9]+ | "'" [^']* "'"
        if ($this->tokenIs(["DEC", "INT", "BOOL", "STR", "NULL"])) {
            $op = $this->token();
            $this->offset++;
            return new Ops\Literal($this->script, $op);
        }

        # look ahead one token to see if this is a function call, if not treat as a varibable
        if ($this->tokenIs("NAME")) {
            if ($this->nextTokenIs("OBR")) { # open bracket
                return $this->compileFunction();
            }
            return $this->compileVar();
        }

        throw new ParseException("Unexpected stuff", $this->script->text, $this->token()[0]);
    }

    # <VALUE> = <LITERAL> | <VAR> | <FNNAME>

    protected function nextTokenIs($ids)
    {
        // only maybe return true if there's another token after the current one
        if ($this->offset + 1 >= sizeof($this->tokens)) {
            return false;
        }
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $code = $this->tokens[$this->offset + 1][1];

        foreach ($ids as $id) {
            if ($id == $code) {
                return true;
            }
        }
        return false;
    }

    # <VAR>   = <OBJECT> "." <FIELD>
    # <OBJECT>= <OBJECTNAME> ( ("->"|"<-") <LINK> )*
    # <LINK>  = <NAME>
    # <OBJECTAME> = <NAME>

    public function compileFunction()
    {
        if (!$this->tokenIs(["NAME"])) {
            throw new ParseException("Expected object name got " . $this->token()[1], $this->script->text, $this->token()[0]);
        }
        $op = $this->token();
        $this->offset++;  // consume NAME

        if (!$this->tokenIs(["OBR"])) {
            throw new ParseException("Expected open bracket", $this->script->text, $this->token()[0]);
        }
        $this->offset++; // consume open bracket

        if ($this->tokenIs(["CBR"])) {
            // if the next token is ")" then this is an empty list!
            $list = new Ops\ExpList($this->script, $this->token(), []);
        } else {
            $list = $this->compileList();
        }

        if (!$this->tokenIs(["CBR"])) {
            throw new ParseException("Expected close bracket", $this->script->text, $this->token()[0]);
        }
        $this->offset++; // consume close bracket

        return new Ops\Call($this->script, $op, new Ops\Name($this->script, $op), $list);
    }

    public function compileList()
    {
        $op = $this->token();
        $exp = $this->compileExpression();
        $list = [$exp];
        while ($this->moreTokens() && $this->tokenIs("COMMA")) {
            $this->offset++; // consume comma
            $exp = $this->compileExpression();
            $list [] = $exp;
        }

        return new Ops\ExpList($this->script, $op, $list);
    }


# <FNCALL>= <FNNAME> "(" <LIST> ")"
# <FNNAME>= <NAME>
# <LIST>  = <EXP> [ "," <LIST> ]

    public function compileVar()
    {
        $object = $this->compileObject();
        if (!$this->tokenIs(["DOT"])) {
            return $object;
        }
        $this->offset++;  // consume DOT
        if (!$this->tokenIs(["NAME"])) {
            throw new ParseException("Expected field name got " . $this->token()[1], $this->script->text, $this->token()[0]);
        }
        $r = new Ops\FieldOf($this->script, $this->token(), $object, new Ops\Name($this->script, $this->token()));
        $this->offset++;  // consume FIELD NAME
        return $r;
    }

    # <LIST> = <EXP> [ "," <LIST> ]

    public function compileObject()
    {
        if (!$this->tokenIs(["NAME"])) {
            throw new ParseException("Expected object name got " . $this->token()[1], $this->script->text, $this->token()[0]);
        }
        $op = $this->token();
        $this->offset++;  // consume NAME
        $r = new Ops\Record($this->script, $op);
        while ($this->tokenIs(["FWD", "BACK"])) {
            $op = $this->token();
            $this->offset++; // consume FWD/BACK
            if (!$this->tokenIs(["NAME"])) {
                throw new ParseException("Expected link name got " . $this->token()[1], $this->script->text, $this->token()[0]);
            }
            $link = $this->token();
            $this->offset++; // consume LINK NAME
            $r = new Ops\Link($this->script, $op, $r, new Ops\Name($this->script, $link));
        }
        return $r;
    }


    /////////////////////////////////////////////////


# operators:
# |
# &
# = <= >= <>
# !
# + -
# * /
# ^
# brackets of course ()
# -> follow link  <- follow backlink
# . access property
# other stuff:
# $object-id
# field-name
# literals

# types:
# integer, decimal, string, boolean

# <EXP>   = <OROP>
# <OROP>  = <ANDOP> [ "|" + <OROP> ]
# <ANDOP> = <CMPOP> [ "&" + <ANDOP> ]
# <CMPOP> = <NOTOP> [ ( "=" | "<>" | ">=" | "<=" | ">" | "<" ) <CMPOP> ]
# <NOTOP> = "!" <NOTOP> | <UMINUSOP>
# <UMINUSOP> = "-" <UMINUSOP> | <ADDOP>
# <ADDOP> = <MULOP> [ ("+"|"-") <ADDOP> ]
# <MULOP> = <POWOP> [ ("*"|"/") <MULOP> ]
# <POWOP> = <BRAOP> [ "^" <POPOP> ]
# <BRAOP> = "(" <EXP> ")" | <VALUE>
# <VALUE> = <LITERAL> | <VAR> | <FNCALL>
# <VAR>   = <OBJECT> "." <FIELD>
# <OBJECT>= <OBJECTNAME> [ ( "->"|"<-" ) <LINK> ]*
# <LINK>  = <NAME>
# <OBJECTAME> = <NAME>
# <LITERAL> = "true" | "false" | "null" | [1-9][0-9]* | [0-9]+ "." [0-9]+ | "'" [^']* "'"
# <FNCALL>= <FNNAME> "(" <LIST> ")"
# <FNNAME>= <NAME>
# <LIST>  = <EXP> [ "," <LIST> ]


}
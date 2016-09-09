<?php

namespace App;

// this class handles the incline scripting compilation and execution
use App\Exceptions\ParseExceptions;

class MMScript 
{
    var $text; // the raw text of the script
    var $expression; // the compiled script
    var $documentRevision; // the compiled script
    protected $tokens; // a list of all the tokens as [ charoffset, codestring, value]
    protected $offset; // the offset through the tokens while compiling
    protected $baseRecordType; // the type at which the route starts
    protected $route; // the links to follow
    var $context; // the types of named objects available to the script
    var $type; // the resulting type of this expression

    public function __construct( $text, $docRev, $context ) {
        $this->text = $text;
        $this->documentRevision = $docRev;
        $this->context = $context;
        $tokeniser = new \App\MMScript\Tokeniser();
        $this->tokens = $tokeniser->tokenise( $text );
        $this->offset = 0;
        $this->expression = $this->compileExpression();
        if( $this->moreTokens() ) {
            throw new ParseException( "Expected additional symbols after end of expression", $this->text, $this->token()[0] );
        }
        $this->type = $this->expression->type( $context );
    }

    function textTree() {
        return $this->expression->treeText();
    }

    function type() {
        return $this->expression->type();
    }

    ///////////////////////////////////////////////// 
    // COMPILE FUNCTIONS
    ///////////////////////////////////////////////// 

    protected function token() {
        if( ! $this->moreTokens() ) { return [ strlen( $this->text ),"END" ]; }
        return $this->tokens[ $this->offset ]; 
    }

    protected function moreTokens() {
        return $this->offset < sizeof( $this->tokens );
    }

    protected function tokenIs( $ids ) {
        if( !$this->moreTokens() ) { return false; }
        if( !is_array( $ids ) ) { $ids = [$ids]; }
        $code = $this->tokens[ $this->offset ][1];
        
        foreach( $ids as $id ) {
            if( $id == $code ) { return true; }
        }
        return false;
    }
    protected function nextTokenIs( $ids ) {
        // only maybe return true if there's another token after the current one 
        if( $this->offset+1 >= sizeof( $this->tokens ) ) { return false; } 
        if( !is_array( $ids ) ) { $ids = [$ids]; }
        $code = $this->tokens[ $this->offset+1 ][1];
        
        foreach( $ids as $id ) {
            if( $id == $code ) { return true; }
        }
        return false;
    }


    # <EXP>   = <OROP>
    public function compileExpression() {
        return $this->compileOr();
    }

    # <OROP>  = <ANDOP> [ "|" + <OROP> ]
    public function compileOr() {
        $left = $this->compileAnd();
        if( $this->moreTokens() && $this->tokenIs( "OR" ) ) {
            $op = $this->token();
            $this->offset++;
            $right = $this->compileOr();
            return new MMScript\Ops\OrOp( $this, $op, $left, $right );
        }
        return $left;
    }

    # <ANDOP> = <CMPOP> [ "&" + <ANDOP> ]
    public function compileAnd() {
        $left = $this->compileCmp();
        if( $this->moreTokens() && $this->tokenIs( "AND" )) {
            $op = $this->token();
            $this->offset++;
            $right = $this->compileAnd();
            return new MMScript\Ops\AndOp( $this, $op, $left, $right );
        }
        return $left;
    }

    # <CMPOP> = <NOTOP> [ ( "=" | "<>" | ">=" | "<=" | ">" | "<" ) <CMPOP> ]
    public function compileCmp() {
        $left = $this->compileNot();
        if( $this->moreTokens() && $this->tokenIs([ "EQ","NEQ","LEQ","GEQ","LT","GT" ])) {
            $op = $this->token();
            $this->offset++;
            $right = $this->compileCmp();
            return new MMScript\Ops\CmpOp( $this, $op, $left, $right );
        }
        return $left;
    }

    # <NOTOP> = "!" <NOTOP> | <ADDOP>
    public function compileNot() {
        if( $this->tokenIs( "NOT" ) ) {
            $op = $this->token();
            $this->offset++;
            $right = $this->compileNot();
            return new MMScript\Ops\NotOp( $this, $op, $right );
        }
        return $this->compileAdd();
    }

    # <ADDOP> = <MULOP> [ ("+"|"-") <ADDOP> ]
    public function compileAdd() {
        $left = $this->compileMul();
        if( $this->moreTokens() && $this->tokenIs([ "PLUS","MIN" ]) ) {
            $op = $this->token();
            $this->offset++;
            $right = $this->compileAdd();
            return new MMScript\Ops\AddOp( $this, $op, $left, $right );
        }
        return $left;
    }

    # <MULOP> = <POWOP> [ ("*"|"/") <MULOP> ]
    public function compileMul() {
        $left = $this->compilePow();
        if( $this->moreTokens() && $this->tokenIs([ "MUL","DIV" ]) ) {
            $op = $this->token();
            $this->offset++;
            $right = $this->compileMul();
            return new MMScript\Ops\MulOp( $this, $op, $left, $right );
        }
        return $left;
    }

    # <POWOP> = <BRAOP> [ "^" <POPOP> ]
    public function compilePow() {
        $left = $this->compileBracket();
        if( $this->moreTokens() && $this->tokenIs([ "POW" ]) ) {
            $op = $this->token();
            $this->offset++;
            $right = $this->compilePow();
            return new MMScript\Ops\PowOp( $this, $op, $left, $right );
        }
        return $left;
    }

    # <BRAOP> = "(" <EXP> ")" | <VALUE>
    public function compileBracket() {
        if( $this->tokenIs([ "OBR" ]) ) {
            $this->offset++; // consume open bracket
            $exp = $this->compileExpression();
            if( ! $this->tokenIs([ "CBR" ]) ) {
                throw new ParseException( "Expected close bracket", $this->text, $this->token()[0] );
            }
            $this->offset++; // consume close bracket
            return $exp;
        }

        return $this->compileValue();
    }

    # <VALUE> = <LITERAL> | <VAR> | <FNNAME>


    public function compileValue() {
        # <LITERAL> = "true" | "false" | [1-9][0-9]* | [0-9]+ "." [0-9]+ | "'" [^']* "'"
        if( $this->tokenIs([ "DEC","INT","BOOL","STR" ]) ){
            $op = $this->token();
            $this->offset++;
            return new MMScript\Ops\Literal( $this, $op );
        }

        # look ahead one token to see if this is a function call, if not treat as a varibable
        if( $this->tokenIs( "NAME" ) ) {
            if( $this->nextTokenIs( "OBR" ) ) { # open bracket
                return $this->compileFunction();
            } 
            return $this->compileVar();
        }
 
        throw new ParseException( "Unexpected stuff", $this->text, $this->token()[0] );
    }

    # <VAR>   = <OBJECT> "." <FIELD>
    # <OBJECT>= <OBJECTNAME> ( ("->"|"<-") <LINK> )*
    # <LINK>  = <NAME>
    # <OBJECTAME> = <NAME>
    public function compileVar() {
        $object = $this->compileObject();
        if( ! $this->tokenIs([ "DOT" ]) ) {
            throw new ParseException( "Expected dot but got ".$this->token()[1], $this->text, $this->token()[0] );
        }
        $this->offset++;  // consume DOT
        if( ! $this->tokenIs([ "NAME" ]) ) {
            throw new ParseException( "Expected field name got ".$this->token()[1], $this->text, $this->token()[0] );
        }
        $r = new MMScript\Ops\FieldOf( $this, $this->token(), $object, new MMScript\Ops\Name( $this, $this->token() ) );
        $this->offset++;  // consume FIELD NAME
        return $r;
    }
         
            
    public function compileObject() {  
        if( ! $this->tokenIs([ "NAME" ]) ) {
            throw new ParseException( "Expected object name got ".$this->token()[1], $this->text, $this->token()[0] );
        }
        $op = $this->token();
        $this->offset++;  // consume NAME
        $r = new MMScript\Ops\Record( $this, $op );
        while( $this->tokenIs([ "FWD","BACK" ]) ) {
            $op = $this->token();
            $this->offset++; // consume FWD/BACK
            if( ! $this->tokenIs([ "NAME" ]) ) {
                throw new ParseException( "Expected link name got ".$this->token()[1], $this->text, $this->token()[0] );
            }
            $link = $this->token();
            $this->offset++; // consume LINK NAME
            $r = new MMScript\Ops\Link( $this, $op, $r, new MMScript\Ops\Name( $this, $link ) );
        }
        return $r;
    }
    
            
# <FNCALL>= <FNNAME> "(" <LIST> ")"
# <FNNAME>= <NAME>
# <LIST>  = <EXP> [ "," <LIST> ]

    public function compileFunction() {
        if( ! $this->tokenIs([ "NAME" ]) ) {
            throw new ParseException( "Expected object name got ".$this->token()[1], $this->text, $this->token()[0] );
        }
        $op = $this->token();
        $this->offset++;  // consume NAME
  
        if( !$this->tokenIs([ "OBR" ]) ) {
            throw new ParseException( "Expected open bracket", $this->text, $this->token()[0] );
        }
        $this->offset++; // consume open bracket

        $list = $this->compileList();

        if( ! $this->tokenIs([ "CBR" ]) ) {
            throw new ParseException( "Expected close bracket", $this->text, $this->token()[0] );
        }
        $this->offset++; // consume close bracket

        return new MMScript\Ops\Call( $this, $op, new MMScript\Ops\Name( $this, $op ), $list );
    }
 
    # <LIST> = <EXP> [ "," <LIST> ]
    public function compileList() {
        $op = $this->token();
        $exp = $this->compileExpression();
        $list = [ $exp ];
        while( $this->moreTokens() && $this->tokenIs("COMMA")) {
            $this->offset++; // consume comma
            $exp = $this->compileExpression();
            $list []= $exp;
        }

	return new MMScript\Ops\ExpList( $this, $op, $list );
    }


    ///////////////////////////////////////////////// 

}

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
# <NOTOP> = "!" <NOTOP> | <ADDOP>
# <ADDOP> = <MULOP> [ ("+"|"-") <ADDOP> ]
# <MULOP> = <POWOP> [ ("*"|"/") <MULOP> ]
# <POWOP> = <BRAOP> [ "^" <POPOP> ]
# <BRAOP> = "(" <EXP> ")" | <VALUE>
# <VALUE> = <LITERAL> | <VAR> | <FNCALL>
# <VAR>   = <OBJECT> "." <FIELD>
# <OBJECT>= <OBJECTNAME> [ ( "->"|"<-" ) <LINK> ]*
# <LINK>  = <NAME>
# <OBJECTAME> = <NAME>
# <LITERAL> = "true" | "false" | [1-9][0-9]* | [0-9]+ "." [0-9]+ | "'" [^']* "'"
# <FNCALL>= <FNNAME> "(" <LIST> ")"
# <FNNAME>= <NAME>
# <LIST>  = <EXP> [ "," <LIST> ]



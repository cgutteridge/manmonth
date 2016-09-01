<?php

namespace App;

use Exception;

// this class handles the incline scripting compilation and execution

class MMScript extends DocumentPart
{
    var $text; // the raw text of the script
    var $expression; // the compiled script
    protected $tokens; // a list of all the tokens as [ charoffset, codestring, value]
    protected $offset; // the offset through the tokens while compiling

    public function __construct( $text ) {
        $this->text = $text;
        $this->tokens = self::tokenise( $text );
        $this->offset = 0;
        $this->expression = $this->compileExp();
        if( $this->moreTokens() ) {
            throw new ParseException( "Expected additional symbols after end of expression", $text, $this->token()[0] );
        }
     
    }

    ///////////////////////////////////////////////// 
    // COMPILE FUNCTIONS
    ///////////////////////////////////////////////// 

    protected function token() {
        return $this->tokens[ $this->offset ]; 
    }

    protected function moreTokens() {
        return $this->offset < sizeof( $this->tokens );
    }

    protected function tokenIs( $ids ) {
        if( !$this->moreTokens() ) { return false; }
        if( !is_array( $ids ) ) { $ids = [$ids]; }
print "".count($this->tokens)."<<SIZE OFF>>".$this->offset."\n";
        foreach( $ids as $id ) {
            if( $id == $this->tokens[ $this->offset ] ) { return true; }
        }
        return false;
    }


    # <EXP>   = <OROP>
    public function compileExp() {
        return $this->compileOr();
    }

    # <OROP>  = <ANDOP> [ "|" + <OROP> ]
    public function compileOr() {
        $left = $this->compileAnd();
        if( $this->moreTokens() && $this->tokenIs( "OR" ) ) {
            $op = $this->token();
            $this->offset++;
            $right = $this->comileOr();
            return new MMScript\OrOp( $op, $left, $right );
        }
        return $left;
    }

    # <ANDOP> = <CMPOP> [ "&" + <ANDOP> ]
    public function compileAnd() {
        $left = $this->compileCmp();
        if( $this->moreTokens() && $this->tokenIs( "AND" )) {
            $op = $this->token();
            $this->offset++;
            $right = $this->comileAnd();
            return new MMScript\AndOp( $op, $left, $right );
        }
        return $left;
    }

    # <CMPOP> = <NOTOP> [ ( "=" | "<>" | ">=" | "<=" | ">" | "<" ) <CMPOP> ]
    public function compileCmp() {
        $left = $this->compileNot();
        if( $this->moreTokens() && $this->tokenIs([ "EQ","NEQ","LEQ","GEQ","LT","GT" ])) {
            $op = $this->token();
            $this->offset++;
            $right = $this->comileCmp();
            return new MMScript\CmpOp( $op, $left, $right );
        }
        return $left;
    }

    # <NOTOP> = "!" <NOTOP> | <ADDOP>
    public function compileNot() {
        if( $this->tokenIs( "NOT" ) ) {
            $op = $this->token();
            $this->offset++;
            $right = $this->comileNot();
            return new MMScript\NotOp( $op, $right );
        }
        return $this->compileAdd();
    }

    # <ADDOP> = <MULOP> [ ("+"|"-") <ADDOP> ]
    public function compileAdd() {
        $left = $this->compileMul();
        if( $this->moreTokens() && $this->tokenIs([ "PLUS","MIN" ]) ) {
            $op = $this->token();
            $this->offset++;
            $right = $this->comileAdd();
            return new MMScript\AddOp( $op, $left, $right );
        }
        return $left;
    }

    # <MULOP> = <POWOP> [ ("*"|"/") <MULOP> ]
    public function compileMul() {
        $left = $this->compilePow();
        if( $this->moreTokens() && $this->tokenIs([ "MUL","DIV" ]) ) {
            $op = $this->token();
            $this->offset++;
            $right = $this->comileMul();
            return new MMScript\MulOp( $op, $left, $right );
        }
        return $left;
    }

    # <POWOP> = <BRAOP> [ "^" <POPOP> ]
    public function compilePow() {
        $left = $this->compileBra();
        if( $this->moreTokens() && $this->tokenIs([ "POW" ]) ) {
            $op = $this->token();
            $this->offset++;
            $right = $this->comilePow();
            return new MMScript\PowOp( $op, $left, $right );
        }
        return $left;
    }

    # <BRAOP> = "(" <EXP> ")" | <VALUE>
    public function compileBra() {
        if( $this->tokenIs([ "OBR" ]) ) {
            $this->offset++; // consume open bracket
            $exp = $this->compileExp();
            if( ! $this->tokenIs([ "CBR" ]) ) {
                throw new ParseException( "Expected close bracket", $text, $this->token()[0] );
            }
            $this->offset++; // consume close bracket
            return $exp;
        }

        return $this->compileValue();
    }

    # <VALUE> = <LITERAL> | <VAR>
    # <VAR>   = <OBJECT> "." <FIELD>
    # <OBJECT>= <OBJECTNAME> [ ( "->"|"<-" ) <LINK> ]*
    # <LINK>  = <NAME>
    # <OBJECTAME> = <NAME>

    public function compileValue() {
        # <LITERAL> = "true" | "false" | [1-9][0-9]* | [0-9]+ "." [0-9]+ | "'" [^']* "'"
        if( $this->tokenIs([ "DEC","INT","BOOL","STR" ]) ){
            $op = $this->token();
            $this->offset++;
            return new MMScript\Literal( $op );
        }

        if( $this->tokenIs( "NAME" ) ) {
            $op = $this->token();
            $this->offset++;  // consume NAME
            $r = new MMScript\MMObject( $op );
            while( $this->token([ "FWD","BACK" ]) ) {
                $op = $this->token();
                $this->offset++; // consume FWD/BACK
                if( ! $this->tokenIs([ "NAME" ]) ) {
                    throw new ParseException( "Expected link name", $text, $this->token()[0] );
                }
                $link = $this->token();
                $this->offset++; // consume LINK NAME
                $r = new MMScript\MMLink( $op, $r, $link );
            }
            return $r;
        }
                
        throw new ParseException( "Unexpected stuff", $text, $this->token()[0] );
    }
            



    ///////////////////////////////////////////////// 

    public static function tokenise( $text ) {
        $offset = 0;
        $tokens = [];
        $len = strlen($text);
        while( $offset < $len ) {  
            $toff = $offset; # the offset of the start of the token
            $c = substr( $text,$offset, 1);
            if( $c==" " || $c=="\n" || $c=="\t" ) { $offset++; continue; }
            if( $c=="|" ) { $offset++; $tokens []= [ $toff, "OR" ]; continue; }
            if( $c=="&" ) { $offset++; $tokens []= [ $toff, "AND" ]; continue; }
            if( $c=="+" ) { $offset++; $tokens []= [ $toff, "PLUS" ]; continue; }
            if( $c=="=" ) { $offset++; $tokens []= [ $toff, "EQ" ]; continue; }
            if( $c=="^" ) { $offset++; $tokens []= [ $toff, "POW" ]; continue; }
            if( $c=="*" ) { $offset++; $tokens []= [ $toff, "MUL" ]; continue; }
            if( $c=="/" ) { $offset++; $tokens []= [ $toff, "DIV" ]; continue; }
            if( $c=="(" ) { $offset++; $tokens []= [ $toff, "OBR" ]; continue; }
            if( $c==")" ) { $offset++; $tokens []= [ $toff, "CBR" ]; continue; }
            if( $c=="." ) { $offset++; $tokens []= [ $toff, "DOT" ]; continue; }
            if( $c>="0" && $c<="9" ) {
                $n = $c;
                $offset++;
                $c = substr( $text,$offset,1 );
                while( $c>="0" && $c<="9" && $offset<$len ) {
                    $n.=$c;
                    $offset++;
                    $c = substr( $text,$offset,1 );
                }
                if( $c != "." ) {
                    $tokens []= [ $toff, "INT", $n ]; 
                    continue;
                }
                # OK it's decimal 
                $offset++;
                $c = substr( $text,$offset,1 );
                $n2 = ""; 
                while( $c>="0" && $c<="9" && $offset<$len) {
                    $n2.=$c;
                    $offset++;
                    $c = substr( $text,$offset,1 );
                }
                if( $n2 == "" ) { 
                    throw new ParseException( "Expected more numbers after the dot", $text, $toff );
                }
                $tokens []= [ $toff, "DEC", $n.".".$n2 ]; 
                continue;
            }
            if( $c=="'" ) {
                $s = "";
                $offset++; // consume leading quote
                $c = substr( $text,$offset,1 );
                while( $c!="'" && $offset<$len ) {
                    if( $c=="\\" ) { 
                        // consume the backslash and char after it
                        $offset++;
                        $c = substr( $text,$offset,1 );
                        $s.= $c; // whatever is after the backslash
                        $offset++;
                        $c = substr( $text,$offset,1 );
                        continue; 
                    }
                    $s.=$c;
                    $offset++;
                    $c = substr( $text,$offset,1 );
                }
                if( $c != "'" ) {
                    throw new ParseException( "Unterminated string literal", $text, $toff );
                }
                $tokens []= [ $toff, "STR", $s ];
                $offset++; // consume ending quote
                continue;
            }
            # first char must be alpha or underscore, others may include numbers
            if( preg_match( "/^[a-zA-Z_]$/", $c )) {
                $s = "";
                while( preg_match( "/^[a-zA-Z0-9_]$/",$c) && $offset<$len ) {
                    $s.=$c;
                    $offset++;
                    $c = substr( $text,$offset,1 );
                }
                // reserved words
                if( $s == "true" ) { 
                    $tokens []= [ $toff, "BOOL", 1 ];
                    continue;
                }
                if( $s == "false" ) { 
                    $tokens []= [ $toff, "BOOL", 0 ];
                    continue;
                }
                // some other term
                $tokens []= [ $toff, "NAME", $s ];
                continue;
            }

            // need to look ahead one to work out -> vs - etc.
            $c2 = substr( $text,$offset, 2);
            if( $c2=="->" ) { $offset+=2; $tokens []= [ "FWD" ]; continue; }
            if( $c2=="<-" ) { $offset+=2; $tokens []= [ "BACK" ]; continue; }
            if( $c2=="<>" ) { $offset+=2; $tokens []= [ "NEQ" ]; continue; }
            if( $c2=="<=" ) { $offset+=2; $tokens []= [ "LEQ" ]; continue; }
            if( $c2==">=" ) { $offset+=2; $tokens []= [ "GEQ" ]; continue; }
            if( $c=="-" ) { $offset++; $tokens []= [ "MIN" ]; continue; }
            if( $c=="<" ) { $offset++; $tokens []= [ "LT" ]; continue; }
            if( $c==">" ) { $offset++; $tokens []= [ "GT" ]; continue; }

            throw new ParseException( "Unexpected character", $text, $toff );
        } 
        return [ "text"=>$text, "tokens"=>$tokens ];
    }
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
# <VALUE> = <LITERAL> | <VAR>
# <VAR>   = <OBJECT> "." <FIELD>
# <OBJECT>= <OBJECTNAME> [ ( "->"|"<-" ) <LINK> ]*
# <LINK>  = <NAME>
# <OBJECTAME> = <NAME>
# <LITERAL> = "true" | "false" | [1-9][0-9]* | [0-9]+ "." [0-9]+ | "'" [^']* "'"

#  

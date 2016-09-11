<?php

namespace App\MMScript;

// this class handles the incline scripting compilation and execution
use App\Exceptions\ParseException;

class Tokeniser {

    public function tokenise( $text ) {
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
            if( $c=="," ) { $offset++; $tokens []= [ $toff, "COMMA" ]; continue; }
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
            if( $c2=="->" ) { $offset+=2; $tokens []= [ $toff, "FWD" ]; continue; }
            if( $c2=="<-" ) { $offset+=2; $tokens []= [ $toff, "BACK" ]; continue; }
            if( $c2=="<>" ) { $offset+=2; $tokens []= [ $toff, "NEQ" ]; continue; }
            if( $c2=="<=" ) { $offset+=2; $tokens []= [ $toff, "LEQ" ]; continue; }
            if( $c2==">=" ) { $offset+=2; $tokens []= [ $toff, "GEQ" ]; continue; }
            if( $c=="-" ) { $offset++; $tokens []= [ $toff, "MIN" ]; continue; }
            if( $c=="<" ) { $offset++; $tokens []= [ $toff, "LT" ]; continue; }
            if( $c==">" ) { $offset++; $tokens []= [ $toff, "GT" ]; continue; }

            throw new ParseException( "Unexpected character", $text, $toff );
        } 
        return $tokens;
    }

}

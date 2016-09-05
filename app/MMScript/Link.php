<?php

namespace App\MMScript;

use App\ScriptException;

class Link extends BinaryOp
{
    // another complex one -- needs to follow the link to find out the new type
    function type() {
        if( @$this->type ) { return $this->type; }
        $this->recordType(); // sets type as a sideEffect
        return $this->type;
    }

    var $recordType;
    function recordType() {
        if( @$this->recordType ) { return $this->recordType; }
        if( $this->right->type() != "#name" ) {
            throw new ScriptException( "Right-value of a ".$this->opCode." must be #name not ".$this->right->type() );
        }

	$leftType = $this->left->recordType();

        $linkName = $this->right->value;
        $link = $this->script->documentRevision->linkTypeByName( $linkName );
        if( !$link ) {
            // not sure what type of exception to make this (Script?)
            throw new ScriptExeception( "Unknown linkname '$linkName'" );
        }
        
        if( $this->opCode == "FWD" ) {
            // check the domain of this link is the right recordtype
            if( $link->domain_sid != $leftType->sid ) {
                throw new ScriptExeception( "Domain of $linkname is not ".$leftType->name );
            } 
            $this->recordType = $link->range;
        } else {
            // backlink, so check range, set type to domain
            if( $link->range_sid != $leftType->sid ) {
                throw new ScriptExeception( "Range of $linkname is not ".$leftType->name );
            } 
            $this->recordType = $link->domain;
        }

        $this->type = $this->recordType->name;
        return $this->recordType;
    }
}

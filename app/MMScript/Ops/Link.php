<?php

namespace App\MMScript\Ops;

use App\Exceptions\ScriptException;

class Link extends BinaryOp
{
    function type() {
        if( @$this->type ) { return $this->type; }
        $this->type = "record";
        return $this->type;
    }

    // another complex one -- needs to follow the link to find out the new type
    var $recordType;
    function recordType() {
        if( @$this->recordType ) { return $this->recordType; }
        if( $this->left->type() != "record" ) {
            throw new ScriptException( "Left-value of a ".$this->opCode." must be record not ".$this->right->type() );
        }
        if( $this->right->type() != "name" ) {
            throw new ScriptException( "Right-value of a ".$this->opCode." must be name not ".$this->right->type() );
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

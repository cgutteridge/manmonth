<?php

namespace App\MMScript;

class Literal extends Op
{
    public function treeText( $prefix = "" ) {
        $r = $prefix.get_class( $this )." :: ".$this->opCode." -> ".$this->opValue." [".@$this->type."]\n";
        return $r;
    }

    public function type() {
        if( @$self->type ) { return $self->type; }
        $map = ['STR'=>'#string','DEC'=>'#decimal','INT'=>'#integer','BOOL'=>'#boolean'];
        $this->type = $map[ $this->opCode ];
        return $this->type;
    }
}

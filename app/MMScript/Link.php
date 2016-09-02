<?php

namespace App\MMScript;

class Link extends BinaryOp
{
    public function treeText( $prefix = "" ) {
        $r = $prefix.$this->op[1]."\n".$this->left->treeText($prefix."  ").$prefix."  ".$this->right[1]." - ".$this->right[2]."\n";
        return $r;
    }
}

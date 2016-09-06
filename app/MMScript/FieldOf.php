<?php

namespace App\MMScript;

use App\ScriptException;

class FieldOf extends BinaryOp
{
    // this type is a biggy... gotta work out the actual type from the schema!
    function type() {
        if( @$this->type ) { return $this->type; }

        $recordType = $this->left->recordType();
        $fieldname = $this->right->value;

        $field = $recordType->field( $fieldname );
        if( !$field ) {
            throw new ScriptException( "Records of type ".$recordType->name." do not have a field named '$fieldname'" );
        }
        $this->type = $field->data["type"];
        return $this->type;
    }
}

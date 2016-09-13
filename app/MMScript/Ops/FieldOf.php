<?php

namespace App\MMScript\Ops;

use App\Exceptions\ScriptException;
use App\MMScript\Values\Value;

/**
 * Class FieldOf
 * @package App\MMScript\Ops
 */
class FieldOf extends BinaryOp
{
    /**
     * @return string
     * @throws ScriptException
     */
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

    /**
     * @param $context
     * @return Value
     */
    function execute($context )
    {
        $leftValue = $this->left->execute($context)->value;
        $rightValue = $this->right->execute($context)->value;
        return $leftValue->getValue( $rightValue );
    }
}

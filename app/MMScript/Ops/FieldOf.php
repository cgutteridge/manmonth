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
    function type()
    {
        if (@$this->type) {
            return $this->type;
        }
        $recordType = $this->left->recordType();
        $fieldname = $this->right->value;
        $field = $recordType->field($fieldname);
        if (!$field) {
            throw new ScriptException("Records of type " . $recordType->name . " do not have a field named '$fieldname'");
        }
        $this->type = $field->data["type"];
        return $this->type;
    }

    /**
     * @param $context
     * @return Value
     * @throws \Exception
     */
    function execute($context)
    {
        /** @var \App\Models\Record $leftValue */
        $leftValue = $this->left->execute($context)->value;
        /** @var string $rightValue */
        $rightValue = $this->right->execute($context)->value;
        $result = $leftValue->getValue($rightValue);
        return $result;
    }
}

<?php

namespace App\MMScript\Ops;

use App\Exceptions\MMScriptRuntimeException;
use App\Exceptions\ScriptException;
use App\Http\TitleMaker;
use App\MMScript\Values\RecordValue;

/**
 * @property Record left
 * @property Name right
 */
class Link extends BinaryOp
{
    var $recordType;

    // another complex one -- needs to follow the link to find out the new type

    function type()
    {
        if (@$this->type) {
            return $this->type;
        }
        $this->type = "record";
        return $this->type;
    }

    function recordType()
    {
        if (@$this->recordType) {
            return $this->recordType;
        }
        if ($this->left->type() != "record") {
            throw new ScriptException("Left-value of a " . $this->opCode . " must be 'record' not '" . $this->right->type() . "'");
        }
        if ($this->right->type() != "name") {
            throw new ScriptException("Right-value of a " . $this->opCode . " must be 'name' not '" . $this->right->type() . "'");
        }

        $leftType = $this->left->recordType();

        $linkName = $this->right->value;
        $link = $this->script->documentRevision->linkTypeByName($linkName);
        if (!$link) {
            // not sure what type of exception to make this (Script?)
            throw new ScriptException("Unknown linkName '$linkName'");
        }

        if ($this->opCode == "FWD") {
            // check the domain of this link is the right recordtype
            if ($link->domain_sid != $leftType->sid) {
                throw new ScriptException("Domain of $linkName is not " . $leftType->name);
            }
            $this->recordType = $link->range();
        } else {
            // backlink, so check range, set type to domain
            if ($link->range_sid != $leftType->sid) {
                throw new ScriptException("Range of $linkName is not " . $leftType->name);
            }
            $this->recordType = $link->domain();
        }

        $this->type = $this->recordType->name;
        return $this->recordType;
    }

    /**
     * @param $context
     * @return RecordValue
     * @throws MMScriptRuntimeException
     */
    function execute($context)
    {
        $titleMaker = new TitleMaker();
        // okay what to do?
        // get the model which is at the other end of a forward/back link
        // of the given type
        // assuming there's only one
        // so...
        /** @var \App\Models\Record $record */
        $record = $this->left->execute($context)->value;

        /** @var string $linkName */
        $linkName = $this->right->execute($context)->value;

        $linkType = $record->documentRevision->linkTypeByName($linkName);

        // hopefully there's one and only one...
        if ($this->opCode == "FWD") {
            $linkedRecords = $record->forwardLinkedRecords($linkType);
            if (!count($linkedRecords)) {
                throw new MMScriptRuntimeException("No record found for forward link: " . $titleMaker->title($linkType));
            }
        } else {
            $linkedRecords = $record->backLinkedRecords($linkType);
            if (!count($linkedRecords)) {
                throw new MMScriptRuntimeException("No record found for backwards link: " . $titleMaker->title($linkType, 'inverse'));
            }
        }

        return new RecordValue($linkedRecords[0]);
    }
}

<?php

namespace App;

// this class wrappers the inline scripting compilation and execution

use App\Models\Record;
use App\Models\RecordType;

class MMScript
{
    var $text; // the raw text of the script
    var $expression; // the compiled script
    var $documentRevision; // the document revision
    var $context; // the types of named objects available to the script
    var $type; // the resulting type of this expression

    /**
     * MMScript constructor.
     * @param $text
     * @param $docRev
     * @param RecordType[] $context array of named recordTypes
     * @throws Exceptions\ParseException
     */
    public function __construct($text, $docRev, $context)
    {
        $this->text = $text;
        $this->documentRevision = $docRev;
        $this->context = $context;
        $compiler = new MMScript\Compiler($this);
        $this->expression = $compiler->compile();
        $this->type = $this->expression->type();
    }

    /**
     * @return string
     */
    function textTree()
    {
        return $this->expression->treeText();
    }

    /**
     * @return string
     */
    function type()
    {
        return $this->expression->type();
    }

    /**
     * @param Record[] $liveContext
     * @return MMScript\Values\Value
     */
    function execute($liveContext)
    {
        return $this->expression->execute($liveContext);
    }
}
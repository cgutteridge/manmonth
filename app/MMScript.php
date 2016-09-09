<?php

namespace App;

// this class wrappers the inline scripting compilation and execution

class MMScript
{
    var $text; // the raw text of the script
    var $expression; // the compiled script
    var $documentRevision; // the compiled script
    var $context; // the types of named objects available to the script
    var $type; // the resulting type of this expression

    public function __construct($text, $docRev, $context)
    {
        $this->text = $text;
        $this->documentRevision = $docRev;
        $this->context = $context;
        $compiler = new MMScript\Compiler($this);
        $this->expression = $compiler->compile();
        $this->type = $this->expression->type();
    }

    function textTree()
    {
        return $this->expression->treeText();
    }

    function type()
    {
        return $this->expression->type();
    }

    function execute($context) {
        return $this->expression->execute( $context );
    }
}
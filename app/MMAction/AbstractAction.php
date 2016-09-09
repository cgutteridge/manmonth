<?php

namespace App\MMAction;

// these classes represent the actions that can be performed as a result of
// a Rule.

/**
 * Class AbstractAction
 * @package App\MMAction
 */
abstract class AbstractAction
{
    // has a name, to use in the rules
    /**
     * @var
     */
    public $name;

    // has a payload
    /**
     * @param array $rreport
     * @param array[AbstractValue] $params
     */
    public abstract function execute(&$rreport, $params );

    // has some parameters with an ordered name & type and human
    // readable title etc.
    /**
     * @var array
     */
    public $params;

    // this will be generated from params
    /**
     * @var array[\App\Fields\Field]
     */
    public $fields;

    /**
     * AbstractAction constructor.
     */
    public function __construct() {
        $this->fields = []; 
        foreach( $this->params as $param ) {
            $this->fields[ $param["name"] ]= \App\Fields\Field::createFromData( $param );
        }
    }



}

<?php

namespace App;

// these classes represent the actions that can be performed as a result of
// a Rule.

abstract class MMAction
{
    // has a name, to use in the rules
    public $name;

    // has a payload
    public abstract function payload( $report, $params );

    // has some parameters with an ordered name & type and human
    // readable title etc.
    public $params;

}

<?php

namespace App\MMAction;

// these classes represent the actions that can be performed as a result of
// a Rule.

class AssignLoad extends \App\MMAction
{
    // has a name, to use in the rules
    public $name = 'assign_load';

    // has a payload
    public function payload( $report, $params ) {
        dd( $params );
    }

    // has some parameters with an ordered name & type and human
    // readable title etc.
    public $params = [
        [ 
            "name"=>"target",
            "type"=>"string",
            "required"=>true,
        ],
        [ 
            "name"=>"category",
            "type"=>"string",
        ],
        [ 
            "name"=>"load",
            "type"=>"decimal",
            "required"=>true,
        ],
        [ 
            "name"=>"description",
            "type"=>"string",
        ],
    ];

}

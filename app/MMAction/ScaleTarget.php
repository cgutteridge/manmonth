<?php

namespace App\MMAction;

// these classes represent the actions that can be performed as a result of
// a Rule.

class ScaleTarget extends \App\MMAction\BaseAction
{
    // has a name, to use in the rules
    public $name = 'scale_target';

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
            "name"=>"factor",
            "type"=>"decimal",
            "required"=>true,
        ],
        [ 
            "name"=>"description",
            "type"=>"string",
        ],
    ];

}

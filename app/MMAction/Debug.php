<?php

namespace App\MMAction;

// these classes represent the actions that can be performed as a result of
// a Rule.

class Debug extends \App\MMAction
{
    // has a name, to use in the rules
    public $name = 'debug';

    // has a payload
    public function payload( $report, $params ) {
        dd( $params );
    }

    // has some parameters with an ordered name & type and human
    // readable title etc.
    public $params = [
        [ 
            "name"=>"message",
            "type"=>"string",
            "required"=>true,
        ],
        [ 
            "name"=>"count",
            "type"=>"integer",
        ]
    ];

}

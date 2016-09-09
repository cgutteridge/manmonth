<?php

namespace App\MMAction;

// these classes represent the actions that can be performed as a result of
// a Rule.

class Debug extends AbstractAction
{
    // has a name, to use in the rules
    public $name = 'debug';

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

    public function execute( &$rreport, $params ) {
        die( "Todo3" );
    }

}

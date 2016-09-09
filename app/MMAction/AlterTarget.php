<?php

namespace App\MMAction;

// these classes represent the actions that can be performed as a result of
// a Rule.

class AlterTarget extends AbstractAction
{
    // has a name, to use in the rules
    public $name = 'alter_target';

    // has some parameters with an ordered name & type and human
    // readable title etc.
    public $params = [
        [ 
            "name"=>"target",
            "type"=>"string",
            "required"=>true,
        ],
        [ 
            "name"=>"change",
            "type"=>"decimal",
            "required"=>true,
        ],
        [ 
            "name"=>"description",
            "type"=>"string",
        ],
    ];

    public function execute( &$rreport, $params ) {
        die( "Todo1" );
    }


}

<?php

namespace App\MMAction;

// these classes represent the actions that can be performed as a result of
// a Rule.

class AssignLoad extends AbstractAction
{
    // has a name, to use in the rules
    public $name = 'assign_load';

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


    /**
     * @param array $rreport
     * @param $params
     */
    public function execute(&$rreport, $params ) {
        $rreport["loads"][] = $params;
        if( !isset($rreport["totals"][$params["target"]])) {
            $rreport["totals"][$params["target"]] = 0;
        }
        $rreport["totals"][$params["target"]] += $params["load"];
        $this->recordLog( $rreport, $params );
    }
}

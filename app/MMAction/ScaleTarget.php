<?php

namespace App\MMAction;

// these classes represent the actions that can be performed as a result of
// a Rule.

use App\Exceptions\ReportingException;

class ScaleTarget extends AbstractAction
{
    // has a name, to use in the rules
    public $name = 'scale_target';

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

    public function execute( &$rreport, $params ) {
        if( !isset($rreport["targets"][$params["target"]])) {
            throw new ReportingException( "Attempt to scale uninitialised target '".$params["target"]."'");
        }
        $rreport["targets"][$params["target"]] *= $params["factor"];
        $this->recordLog( $rreport, $params );
    }
}

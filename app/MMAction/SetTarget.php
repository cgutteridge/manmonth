<?php

namespace App\MMAction;

use App\RecordReport;

// these classes represent the actions that can be performed as a result of
// a Rule.

class SetTarget extends Action
{
    // has a name, to use in the rules
    public $name = 'set_target';

    // has some parameters with an ordered name & type and human
    // readable title etc.
    public $params = [
        [ 
            "name"=>"target",
            "type"=>"string",
            "required"=>true,
        ],
        [ 
            "name"=>"value",
            "type"=>"decimal",
            "required"=>true,
        ],
        [ 
            "name"=>"description",
            "type"=>"string",
        ],
    ];

    /**
     * @param RecordReport $recordReport
     * @param $params
     */
    public function execute($recordReport, $params ) {
        $recordReport->setLoadingTarget( $params["target"], $params["value"] );
        $this->recordLog( $recordReport, $params );
    }
}

<?php

namespace App\MMAction;

use App\RecordReport;

class AssignLoad extends Action
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
     * @param RecordReport $recordReport
     * @param $params
     */
    public function execute($recordReport, $params ) {
        if( $params["load"] != 0 ) {
            $total = $recordReport->getLoadingTotal($params["target"]);
            $recordReport->setLoadingTotal($params["target"], $total + $params["load"]);
            $recordReport->appendLoading($params);
        }
        // always log that we got this far if we passed the trigger rule
        $this->recordLog( $recordReport, $params );
    }
}

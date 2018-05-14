<?php

namespace App\MMAction;

use App\Models\Rule;
use App\RecordReport;

// these classes represent the actions that can be performed as a result of
// a Rule.

class SetTarget extends Action
{
    // has a name, to use in the rules
    public $name = 'set_target';
    public $action_type = 'targets';

    // has some parameters with an ordered name & type and human
    // readable title etc.
    public $params = [
        [
            "name" => "value",
            "type" => "decimal",
            "required" => true,
        ],
        [
            "name" => "description",
            "type" => "string",
        ],
        [
            "name" => "unit",
            "type" => "string",
        ],
        [
            "name" => "units",
            "type" => "string",
        ]
    ];

    /**
     * @param RecordReport $recordReport
     * @param Rule $rule
     * @param array $context
     * @param $params
     */
    public function execute($recordReport, $rule, $context, $params)
    {
        $recordReport->setLoadingTarget($params["value"]);
        if (array_key_exists("description", $params)) {
            $recordReport->setOption("description", $params["description"]);
        }
        if (array_key_exists("unit", $params)) {
            $recordReport->setOption("unit", $params["unit"]);
        }
        if (array_key_exists("units", $params)) {
            $recordReport->setOption("units", $params["units"]);
        }
        $this->recordLog($recordReport, $params);
    }
}

<?php

namespace App\MMAction;

// these classes represent the actions that can be performed as a result of
// a Rule.

use App\Exceptions\ReportingException;
use App\Models\Rule;
use App\RecordReport;

class ScaleTarget extends Action
{
    // has a name, to use in the rules
    public $name = 'scale_target';

    // has some parameters with an ordered name & type and human
    // readable title etc.
    public $params = [
        [
            "name" => "target",
            "type" => "string",
            "required" => true,
        ],
        [
            "name" => "factor",
            "type" => "decimal",
            "required" => true,
        ],
        [
            "name" => "description",
            "type" => "string",
        ],
    ];


    /**
     * @param RecordReport $recordReport
     * @param Rule $rule
     * @param array $context
     * @param array $params
     * @throws ReportingException
     */
    public function execute($recordReport, $rule, $context, $params)
    {
        if (!$recordReport->hasLoadingTarget($params["target"])) {
            throw new ReportingException("Attempt to scale uninitialised target '" . $params["target"] . "'");
        }
        $value = $recordReport->getLoadingTarget($params["target"]) * $params["factor"];
        $recordReport->setLoadingTarget($params["target"], $value);
        $this->recordLog($recordReport, $params);
    }
}

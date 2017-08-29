<?php

namespace App\MMAction;

use App\Exceptions\ReportingException;
use App\Models\Rule;
use App\RecordReport;

class AlterTarget extends Action
{
    // has a name, to use in the rules
    public $name = 'alter_target';

    // has some parameters with an ordered name & type and human
    // readable title etc.
    public $params = [
        [
            "name" => "change",
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
     * @param $params
     * @throws ReportingException
     */
    public function execute($recordReport, $rule, $context, $params)
    {
        if (!isset($params["change"])) {
            throw new ReportingException("Attempt to alter target '" . $params["target"] . "' with null change");
        }
        /** @var float $value */
        $value = $recordReport->getLoadingTarget() * $params["factor"];
        $recordReport->setLoadingTarget($value);
        $this->recordLog($recordReport, $params);
    }
}

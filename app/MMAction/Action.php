<?php

namespace App\MMAction;

use App\Fields\Field;
use App\Models\Rule;
use App\RecordReport;

/**
 * Class AbstractAction
 * @package App\MMAction
 */
abstract class Action
{
    /**
     * @var
     */
    public $name;

    /**
     * Has some parameters with an ordered name & type and human readable title etc.
     * @var array
     */
    public $params;


    /**
     * This will be generated from params
     * @var array[\App\Fields\Field]
     */
    public $fields;

    /**
     * AbstractAction constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->fields = [];
        foreach ($this->params as $param) {
            $this->fields[$param["name"]] = Field::createFromData($param);
        }
    }

    /**
     * @param RecordReport $recordReport
     * @param Rule $rule
     * @param array $context
     * @param array $params
     */
    public abstract function execute($recordReport, $rule, $context, $params);

    /**
     * @param \App\RecordReport $recordReport
     * @param $params - params for the action to be logged
     * @internal param $rreport - report to write log to
     */
    protected function recordLog($recordReport, $params)
    {
        $recordReport->appendLog(
            ["action" => $this->name, "params" => $params]
        );
    }

}

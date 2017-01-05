<?php

namespace App\MMAction;

use App\Models\Record;
use App\Models\Rule;
use App\RecordReport;

class AssignLoad extends Action
{
    // has a name, to use in the rules
    public $name = 'assign_load';

    // has some parameters with an ordered name & type and human
    // readable title etc.
    public $params = [
        [
            "name" => "category",
            "type" => "string",
        ],
        [
            "name" => "load",
            "type" => "decimal",
            "required" => true,
        ],
        [
            "name" => "description",
            "type" => "string",
        ],
        [
            "name" => "link",
            "type" => "record"
        ]
    ];

    /**
     * @param RecordReport $recordReport
     * @param Rule $rule
     * @param array $context
     * @param array $params
     */
    public function execute($recordReport, $rule, $context, $params)
    {
        if ($params["load"] != 0) {
            $total = $recordReport->getLoadingTotal();
            $recordReport->setLoadingTotal($total + $params["load"]);
            $params["rule_title"] = $rule->data['title'];
            if (isset($params["link"])) {
                /** @var Record $linkRecord */
                $linkRecord = $params["link"];
                $params["record_id"] = $linkRecord->id;
                unset($params["link"]);
            }
            if (!array_key_exists('category', $params)) {
                $params['category'] = 'null';
            }
            $recordReport->appendLoading($params);
        }
        // always log that we got this far if we passed the trigger rule
        $this->recordLog($recordReport, $params);
    }
}

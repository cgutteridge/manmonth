<?php
/**
 * Created by PhpStorm.
 * User: cjg
 * Date: 10/09/2016
 * Time: 19:53
 */

namespace App\MMAction;

// these classes represent the actions that can be performed as a result of
// a Rule.

use App\Models\Rule;
use App\RecordReport;

class SetDecimalColumn extends Action
{
    // has a name, to use in the rules
    public $name = 'set_decimal_column';
    public $action_type = 'columns';

    // has some parameters with an ordered name & type and human
    // readable title etc.
    public $params = [
        [
            "name" => "column",
            "type" => "string",
            "required" => true,
        ],
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
            "name" => "total",
            "type" => "boolean"
        ],
        [
            "name" => "mean",
            "type" => "boolean"
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
        $recordReport->setColumn($params["column"], $params["value"], $params["total"], $params["mean"]);
        $this->recordLog($recordReport, $params);
    }
}

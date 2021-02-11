<?php
/**
 * Created by PhpStorm.
 * User: cjg
 * Date: 10/09/2016
 * Time: 19:53
 */

namespace App\MMAction;

use App\Models\Rule;
use App\RecordReport;

// these classes represent the actions that can be performed as a result of
// a Rule.

class SetStringColumn extends Action
{
    // has a name, to use in the rules
    public $name = 'set_string_column';
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
            "type" => "string",
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
     */
    public function execute($recordReport, $rule, $context, $params)
    {
        $recordReport->setColumn($params["column"], $params["value"]);
        $this->recordLog($recordReport, $params);
    }
}

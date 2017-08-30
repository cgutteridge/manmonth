<?php

namespace App\MMAction;

// these classes represent the actions that can be performed as a result of
// a Rule.

use App\Exceptions\ReportingException;
use App\Models\Rule;
use App\RecordReport;

class AddCategory extends Action
{
    // has a name, to use in the rules
    public $name = 'add_category';

    // has some parameters with an ordered name & type and human
    // readable title etc.
    public $params = [
        [
            "name" => "category",
            "type" => "string",
            "required" => true,
        ],
        [
            "name" => "label",
            "type" => "string"
        ],
        [
            "name" => "description",
            "type" => "string",
        ],
        [
            "name" => "background_color",
            "type" => "string",
        ],
        [
            "name" => "text_color",
            "type" => "string",
        ],
        [
            "name" => "show_column",
            "type" => "boolean"
        ]
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
        foreach ($params as $param => $value) {
            if ($param == 'category') {
                continue;
            }
            $recordReport->setOption( 'category_'.$param."_".$params['category'], $value );
        }
        $recordReport->setOption( 'category_exists_'.$params['category'], true );

        $this->recordLog($recordReport, $params);
    }
}

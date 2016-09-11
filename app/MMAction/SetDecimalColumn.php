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

use App\RecordReport;

class SetDecimalColumn extends AbstractAction
{
    // has a name, to use in the rules
    public $name = 'set_decimal_column';

    // has some parameters with an ordered name & type and human
    // readable title etc.
    public $params = [
        [
            "name"=>"column",
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
    public function execute( $recordReport, $params ) {
        $recordReport->setColumn( $params["column"], $params["value"] );
        $this->recordLog( $recordReport, $params );
    }
}

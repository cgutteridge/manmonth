<?php
/**
 * Created by PhpStorm.
 * User: cjg
 * Date: 30/09/2016
 * Time: 12:53
 */

namespace App\Http;

use Carbon\Carbon;

class DateMaker
{
    /**
     * @param Carbon $date
     * @return string
     */
    function dateTime($date)
    {
        return $date->format('D d M Y, H:i');
    }
}
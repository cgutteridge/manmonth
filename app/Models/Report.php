<?php
/**
 * Created by PhpStorm.
 * User: cjg
 * Date: 10/09/2016
 * Time: 20:10
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property array data
 */
class Report extends Model
{
    public $timestamps = false;
    public $recordReports = [];

    public function documentRevision()
    {
        return $this->belongsTo('App\Models\DocumentRevision');
    }

}



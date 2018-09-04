<?php
/**
 * Created by PhpStorm.
 * User: cjg
 * Date: 29/08/2018
 * Time: 16:32
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class RoleCondition extends Model
{
    public $timestamps = false;
    protected $casts = [
        "condition" => "array"
    ];

}

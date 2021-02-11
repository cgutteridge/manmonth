<?php
/**
 * Created by PhpStorm.
 * User: cjg
 * Date: 29/08/2018
 * Time: 16:32
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleCondition extends Model
{
    public $timestamps = false;
    protected $casts = [
        "condition" => "array"
    ];

    /*************************************
     * RELATIONSHIPS
     *************************************/

    /**
     * @return BelongsTo
     */
    function role()
    {
        return $this->belongsTo(Role::class);
    }
}

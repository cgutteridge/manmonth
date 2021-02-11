<?php
/**
 * Created by PhpStorm.
 * User: cjg
 * Date: 28/08/2018
 * Time: 16:55
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string name
 * @property string label
 * @property Document document
 */
class RoleUser extends Model
{
    public $timestamps = false;
}


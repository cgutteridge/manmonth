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
class ExtendedUser extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'username';
    protected $keyType = 'string';
    public $incrementing = false;
    public $table = "imported_users";
}


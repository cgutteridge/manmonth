<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string name
 * @property string label
 * @property Collection roles
 * @property bool global
 */
class Permission extends Model
{
    public $timestamps = false;

    public static function globalPermissions()
    {
        return Permission::where('global', 1)->orderBy('name', 'desc')->get();
    }

    public static function documentPermissions()
    {
        return Permission::where('global', 0)->orderBy('name', 'desc')->get();
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}

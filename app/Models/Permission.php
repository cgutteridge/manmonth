<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string name
 * @property string label
 * @property Collection roles
 * @property bool global
 */
class Permission extends Model
{
    public $timestamps = false;

    /*************************************
     * RELATIONSHIPS
     *************************************/

    /**
     * @return BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /*************************************
     * READ FUNCTIONS
     *************************************/

    public static function globalPermissions()
    {
        $relationCode = "Permission->globalPermissions";
        if (!array_key_exists($relationCode, MMModel::$cache)) {
            /** @noinspection PhpUndefinedMethodInspection */
            MMModel::$cache[$relationCode] = Permission::where('global', 1)
                ->orderBy('name', 'desc')->get();
        }
        return MMModel::$cache[$relationCode];
    }

    public static function documentPermissions()
    {
        $relationCode = "Permission->documentPermissions";
        if (!array_key_exists($relationCode, MMModel::$cache)) {
            /** @noinspection PhpUndefinedMethodInspection */
            MMModel::$cache[$relationCode] = Permission::where('global', 0)
                ->orderBy('name', 'desc')->get();
        }
        return MMModel::$cache[$relationCode];
    }


}

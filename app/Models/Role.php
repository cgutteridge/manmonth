<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string name
 * @property string label
 * @property Document document
 * @property Collection roleConditions
 * @method static where(string $string, string $string1)
 */
class Role extends Model
{
    public $timestamps = false;

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * @param Permission|string $permission
     * @return Model
     */
    public function assign($permission)
    {
        if (is_string($permission)) {
            $permissionObj = Permission::whereName($permission)->firstOrFail();
            return $this->permissions()->save($permissionObj);
        }

        return $this->permissions()->save($permission);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function roleConditions()
    {
        return $this->hasMany(RoleCondition::class);
    }

    public function users() {
        return $this->belongsToMany(User::class, 'role_user', 'role_id', 'user_username');
    }
}

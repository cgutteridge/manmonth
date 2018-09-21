<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string name
 * @property string label
 * @property Document document
 */
class Role extends Model
{
    public $timestamps = false;

    /*************************************
     * RELATIONSHIPS
     *************************************/

    /**
     * @return BelongsTo
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * @return BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * @return HasMany
     */
    public function roleCondition()
    {
        return $this->hasMany(RoleCondition::class);
    }

    /**
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /*************************************
     * ACTIONS FUNCTIONS
     *************************************/

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

}

<?php

namespace App\Models;

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

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}

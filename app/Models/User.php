<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property Collection roles
 * @property string name
 * @property string email
 * @property string password
 */
class User extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * @param Role|Collection $role
     * @return bool
     */
    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }

        // assume $role is a collection of roles then.
        return ($role->intersect($this->roles)->count() > 0);
    }

    /**
     * @param Role $role
     * @param Document $document
     * @return bool
     */
    public function hasDocumentRole($role, $document)
    {
        if (is_string($role)) {
            return $this->documentRoles($document)->contains('name', $role);
        }

        // assume $role is a collection of roles then.
        return ($role->intersect($this->documentRoles($document))->count() > 0);
    }

    /**
     * @param Document $document
     * @return Collection mixed
     */
    public function documentRoles($document)
    {
        return $this->roles()->where('document_id', $document->id)->get();
    }

    /**
     *
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * @param string|Role $role
     * @return Model
     */
    public function assign($role)
    {
        if (is_string($role)) {
            $roleObj = Role::whereName($role)->firstOrFail();
            return $this->roles()->save($roleObj);
        }

        // it's a Role object then.
        return $this->roles()->save($role);
    }


}

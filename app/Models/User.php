<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property Collection roles
 * @property string name
 * @property string username
 * @property string password
 */
class User extends Authenticatable
{
    protected $primaryKey = 'username';
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'username', 'password',
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
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function extended()
    {
        return $this->hasOne(ExtendedUser::class, 'username', 'username');
    }

    /**
     * Return the relationship to related roles for this use.
     * Does not include roles from rules.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_username');
    }

    /**
     * True if this user has a role, without any limitation by a specific document
     * Used for roles that apply to the entire system.
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
     * @param Role|Collection $role
     * @param Document $document
     * @return bool
     */
    public function hasDocumentRole($role, $document)
    {
        /*
        if (is_string($role)) {
            // this feature doesn't seem to be used yet.
            return $this->documentRoles($document)->contains('name', $role);
        }
*/
        // assume $role is a collection of roles then.
        return ($role->intersect($this->documentRoles($document))->count() > 0);
    }

    private $documentRoles = [];

    /**
     * Return all roles for the user, including those from conditions
     * With attached documents, if available
     * @return array Role
     */
    public function allRoles()
    {
        $roles = [];
        foreach ($this->roles()->with('document')->get() as $role) {
            $roles[$role->id] = $role;
        };
        $roleConditions = RoleCondition::where('id', '>', 0)->with('role.document')->get();
        foreach ($roleConditions as $roleCondition) {
            if ($this->matchesCondition($roleCondition->condition)) {
                $role = $roleCondition->role;
                if ($role) {
                    $roles[$role->id] = $role;
                }
            }
        }

        return $roles;
    }

    /**
     * @param Document $document
     * @return array Role
     */
    public
    function documentRoles($document)
    {
        if (!array_key_exists($document->id, $this->documentRoles)) {
            $matchedRoles = [];
            foreach ($this->roles()->where('document_id', $document->id)->get() as $role) {
                $matchedRoles[$role->id] = $role;
            }

            // get roles based on rules
            $rolesWithConditions = $document->roles()->with('roleCondition')->get();
            foreach ($rolesWithConditions as $roleWithConditions) {
                foreach ($roleWithConditions->roleCondition as $roleCondition) {
                    if ($this->matchesCondition($roleCondition->condition)) {
                        $matchedRoles[$roleWithConditions->id] = $roleWithConditions;
                        // no need to check other conditions for the same role if this matched
                        continue;
                    }
                }
            }
            $this->documentRoles[$document->id] = $matchedRoles;
        }
        return $this->documentRoles[$document->id];
    }

    public
    function matchesCondition(array $condition)
    {
        $extendedData = $this->extended->toArray();
        foreach ($condition as $key => $value) {
            if (!array_key_exists($key, $extendedData)) {
                return false;
            }
            if ($extendedData[$key] != $value) {
                return false;
            }

        }
        return true;
    }


    /**
     * @param string|Role $role
     * @return Model
     */
    public
    function assign($role)
    {
        if (is_string($role)) {
            $roleObj = Role::whereName($role)->firstOrFail();
            return $this->roles()->save($roleObj);
        }

        // it's a Role object then.
        return $this->roles()->save($role);
    }


}

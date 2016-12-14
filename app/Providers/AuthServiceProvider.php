<?php

namespace App\Providers;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Schema;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @param  \Illuminate\Contracts\Auth\Access\Gate $gate
     * @return void
     */
    public function boot(GateContract $gate)
    {
        $this->registerPolicies($gate);
        /** @var Permission $permission */

        foreach ($this->getPermissions() as $permission) {
            if ($permission->global) {
                $gate->define(
                    $permission->name,
                    /**
                     * @param User $user
                     * @return boolean
                     */
                    function ($user) use ($permission) {
                        return $user->hasRole($permission->roles);
                    });
            } else {
                $gate->define(

                    $permission->name,
                    /**
                     * @param User $user
                     * @return boolean
                     */
                    function ($user, $document) use ($permission) {
                        return $user->hasDocumentRole($permission->roles, $document);
                    });
            }

        }
    }

    protected function getPermissions()
    {
        // TODO really needs to know the document, or does it?

        // If the DB is not yet setup, we can't get any permissions!
        if (!Schema::hasTable('permissions')) {
            return new Collection();
        }

        return Permission::with('roles')->get();
    }
}

<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

class AddSuperuser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // add role
        $role = new Role();
        $role->name = "sysadmin";
        $role->label = "System Adminstrator";
        $role->save();
        // link role to permissions
        $permissions = Permission::where('name', 'full-user-admin')
            ->orWhere('name', 'full-document-admin')
            ->get();
        $role->permissions()->attach($permissions);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // remove role from users
        // unlink role to permissions should happen when we remove the role
        // remove role
        Role::where('name', 'sysadmin')->delete();
    }
}

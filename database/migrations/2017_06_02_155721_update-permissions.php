<?php

use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;

class UpdatePermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permVPL = new Permission();
        $permVPL->name = "view-published-latest";
        $permVPL->label = "View current published document revision";
        $permVPL->save();

        $permVP = new Permission();
        $permVP->name = "view-published";
        $permVP->label = "View any published document revision";
        $permVP->save();

        $permVC = Permission::where('name', 'view-current')->first();

        // convert any view-current to view-published-latest
        DB::table("permission_role")
            ->where('permission_id', $permVC->id)
            ->update(['permission_id' => $permVPL->id]);

        $permVC->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $permVC = new Permission();
        $permVC->name = "view-current";
        $permVC->label = "View current published document";
        $permVC->save();

        $permVPL = Permission::where('name', 'view-published-latest')->first();
        $permVP = Permission::where('name', 'view-published')->first();

        // convert any view-published-latest to view-current
        DB::table("permission_role")
            ->where('permission_id', $permVPL->id)
            ->update(['permission_id' => $permVC->id]);

        $permVPL->delete();
        $permVP->delete();
    }
}

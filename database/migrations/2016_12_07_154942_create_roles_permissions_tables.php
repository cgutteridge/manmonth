<?php

use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRolesPermissionsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('label')->nullable();
            $table->integer('document_id')->nullable(); // roles belong to a document
            // maybe later there will be a document series to which
            // roles are assigned?
        });
        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('label')->nullable();
            $table->boolean('global')->default(false);
        });
        Schema::create('permission_role', function (Blueprint $table) {
            $table->integer('role_id')->unsigned();
            $table->integer('permission_id')->unsigned();

            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');
            $table->foreign('permission_id')
                ->references('id')
                ->on('permissions')
                ->onDelete('cascade');

            $table->primary(['role_id', 'permission_id']);
        });
        Schema::create('role_user', function (Blueprint $table) {
            $table->integer('role_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->primary(['role_id', 'user_id']);
        });

        // permissions

        $perm = new Permission();
        $perm->name = "view-current";
        $perm->label = "View current published document";
        $perm->save();

        $perm = new Permission();
        $perm->name = "view-archive";
        $perm->label = "View previous published versions of document";
        $perm->save();

        $perm = new Permission();
        $perm->name = "view-draft";
        $perm->label = "View current draft version of document";
        $perm->save();

        $perm = new Permission();
        $perm->name = "view-scrap";
        $perm->label = "View scrapped draft versions of document";
        $perm->save();

        $perm = new Permission();
        $perm->name = "create-draft";
        $perm->label = "Create document draft";
        $perm->save();

        $perm = new Permission();
        $perm->name = "publish";
        $perm->label = "Publish document";
        $perm->save();

        $perm = new Permission();
        $perm->name = "scrap";
        $perm->label = "Scrap a draft document";
        $perm->save();

        $perm = new Permission();
        $perm->name = "edit-data";
        $perm->label = "Edit document data";
        $perm->save();

        /*
        $perm = new Permission();
        $perm->name = "create-records";
        $perm->label = "Create new records";
        $perm->save();

        $perm = new Permission();
        $perm->name = "delete-records";
        $perm->label = "Delete records";
        $perm->save();

        $perm = new Permission();
        $perm->name = "create-links";
        $perm->label = "Create new links";
        $perm->save();

        $perm = new Permission();
        $perm->name = "delete-links";
        $perm->label = "Delete links";
        $perm->save();
        */

        $perm = new Permission();
        $perm->name = "edit-schema";
        $perm->label = "Edit schema";
        $perm->save();

        $perm = new Permission();
        $perm->name = "edit-reports";
        $perm->label = "Edit reports";
        $perm->save();

        $perm = new Permission();
        $perm->name = "edit-permissions";
        $perm->label = "View and edit user roles and permissions on this document";
        $perm->save();

        /* GLOBAL PERMISSIONS */

        $perm = new Permission();
        $perm->name = "full-user-admin";
        $perm->label = "Full User Admin Rights";
        $perm->global = true;
        $perm->save();

        $perm = new Permission();
        $perm->name = "full-document-admin";
        $perm->label = "Full Document Admin Rights";
        $perm->global = true;
        $perm->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('permission_role');
        Schema::drop('role_user');
        Schema::drop('permissions');
        Schema::drop('roles');
    }
}

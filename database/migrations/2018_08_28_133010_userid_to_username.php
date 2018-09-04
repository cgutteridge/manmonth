<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UseridToUsername extends Migration
{
    /**
     * Run the migrations.
     *
     * We're shifting role_user to use username instead of userid
     *
     * @return void
     */
    public function up()
    {
        Schema::table('role_user', function (Blueprint $table) {
            $table->string('user_username');
        });
        $map = [];
        foreach (DB::table("users")->get() as $user) {
            $map[$user->id]=$user->username;
        }
        foreach (DB::table("role_user")->get() as $role_user) {
            DB::table("role_user")
                ->where("role_id", $role_user->role_id)
                ->where("user_id", $role_user->user_id)
                ->update(["user_username" => $map[$role_user->user_id]]);
        }
        Schema::table('role_user', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['role_id']);
            $table->dropPrimary();
            $table->primary(['role_id', 'user_username']);
            $table->dropColumn('user_id');
	});
    }

    /**
     * Reverse the migrations. Restores structure, not data.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('role_user', function (Blueprint $table) {
            $table->dropPrimary();
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->primary(['role_id', 'user_id']);
            $table->dropColumn('user_username');
        });
    }
}

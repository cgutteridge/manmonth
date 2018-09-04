<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EmailToUsername extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username');
            $table->string('source');
        });
        foreach (DB::table("users")->get() as $user) {
            $email_bits = preg_split( '/@/', $user->email );
            DB::table("users")
                ->where("id", $user->id)
                ->update(array("username" => $email_bits[0] ));
        }
        // mark it as unique only after populating it with unique values
        Schema::table('users', function (Blueprint $table) {
            $table->unique( 'username' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn("username");
            $table->dropColumn("source");
        });
    }
}

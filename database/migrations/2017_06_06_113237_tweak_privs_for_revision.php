<?php

use Illuminate\Database\Migrations\Migration;

class TweakPrivsForRevision extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('permissions')
            ->where('name', 'scrap')
            ->update(['name' => 'commit', 'label' => 'Commit or scrap document revisions']);
        DB::table('permissions')
            ->where('name', 'publish')
            ->update(['label' => 'Publish and unpublish document revisions']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('permissions')
            ->where('name', 'commit')
            ->update(['name' => 'commit', 'label' => 'Scrap a draft document']);
        DB::table('permissions')
            ->where('name', 'publish')
            ->update(['label' => 'Publish document']);
    }
}

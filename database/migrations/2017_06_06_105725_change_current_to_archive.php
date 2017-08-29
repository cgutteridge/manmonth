<?php

use Illuminate\Database\Migrations\Migration;

class ChangeCurrentToArchive extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table("document_revisions")
            ->where('status', 'current')
            ->update(['status' => 'archive']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // tricky to downgrade.
        #dd("Downgrade not currently available");
    }
}

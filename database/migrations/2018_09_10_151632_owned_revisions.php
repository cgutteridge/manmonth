<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class OwnedRevisions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @throws Exception
     */
    public function up()
    {
        $drafts = \App\Models\DocumentRevision::where('status','draft');
        if( $drafts->count() ) {
            throw new Exception("Please commit ".$drafts->count()." draft revisions before this migration.");
        }
        Schema::table('document_revisions', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('document_revisions', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
}

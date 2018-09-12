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
            $table->string('user_username');
            $table->text('comment' );
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
            $table->dropColumn('user_username');
            $table->dropColumn('comment');
        });
    }
}

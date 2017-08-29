<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class ModifyDocumentRevisionAddPublished extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('document_revisions', function (Blueprint $table) {
            $table->boolean("published");
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
            $table->dropColumn("published");
        });
    }
}

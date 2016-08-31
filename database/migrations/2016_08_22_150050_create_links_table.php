<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('links', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sid')->unsigned();
            $table->integer('document_revision_id')->unsigned();
            $table->index(['document_revision_id', 'sid'],'rev_sid');

            $table->integer('subject_sid')->unsigned();
            $table->index(['document_revision_id', 'subject_sid'],'subject_rev_sid');
            $table->integer('link_type_sid')->unsigned();
            $table->index(['document_revision_id', 'link_type_sid'],'link_type_rev_sid');
            $table->integer('object_sid')->unsigned();
            $table->index(['document_revision_id', 'object_sid'],'object_rev_sid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('links');
    }
}

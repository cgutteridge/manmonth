<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecordTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('record_types', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sid')->unsigned();
            $table->integer('document_revision_id')->unsigned();
            $table->index(['document_revision_id', 'sid'],'rev_sid');

            $table->string('name');
            $table->string('label');
            $table->string('title_script');
            $table->string('external_table');
            $table->string('external_key');
            $table->string('external_local_key');

            $table->text('data');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('record_types');
    }
}


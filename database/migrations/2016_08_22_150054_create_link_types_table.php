<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLinkTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('link_types', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sid')->unsigned();
            $table->integer('document_revision_id')->unsigned();
            $table->index(['document_revision_id', 'sid'],'rev_sid');

            $table->integer('domain_sid')->unsigned();
            $table->index(['document_revision_id', 'domain_sid'],'domain_rev_sid');
            $table->integer('domain_min')->unsigned();
            $table->integer('domain_max')->unsigned()->nullable();

            $table->integer('range_sid')->unsigned();
            $table->index(['document_revision_id', 'range_sid'],'range_rev_sid');
            $table->integer('range_min')->unsigned();
            $table->integer('range_max')->unsigned()->nullable();

            $table->string('name');
            $table->string('label');
            $table->string('inverse_label');
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
        Schema::drop('link_types');
    }
}

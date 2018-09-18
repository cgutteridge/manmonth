<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RefactorCompoundKeys extends Migration
{
    /**
     * Run the migrations.
     * Add the _id fields to replace _sid fields
     * Next migration actually populates them
     *
     * @return void
     */
    public function up()
    {
        // add in _id columns for every column that's currently a _sid
        Schema::table('link_types', function (Blueprint $table) {
            $table->integer('domain_id')->unsigned();
            $table->integer('range_id')->unsigned();
            $table->index('domain_id');
            $table->index('range_id');
        });
        Schema::table('links', function (Blueprint $table) {
            $table->integer('subject_id')->unsigned();
            $table->integer('link_type_id')->unsigned();
            $table->integer('object_id')->unsigned();
            $table->index('subject_id');
            $table->index('link_type_id');
            $table->index('object_id');
        });
        // record_type has no sid links
        Schema::table('records', function (Blueprint $table) {
            $table->integer('record_type_id')->unsigned();
            $table->index('record_type_id');
        });
        Schema::table('report_types', function (Blueprint $table) {
            $table->integer('base_record_type_id')->unsigned();
            $table->index('base_record_type_id');
        });
        Schema::table('reports', function (Blueprint $table) {
            $table->integer('report_type_id')->unsigned();
            $table->index('report_type_id');
        });
        Schema::table('rules', function (Blueprint $table) {
            $table->integer('report_type_id')->unsigned();
            $table->index('report_type_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public
    function down()
    {
        Schema::table('link_types', function (Blueprint $table) {
            $table->dropColumn("domain_id");
            $table->dropColumn("range_id");
        });
        Schema::table('links', function (Blueprint $table) {
            $table->dropColumn("subject_id");
            $table->dropColumn("link_type_id");
            $table->dropColumn("object_id");
        });
        // record_type has no sid links
        Schema::table('records', function (Blueprint $table) {
            $table->dropColumn("record_type_id");
        });
        Schema::table('report_types', function (Blueprint $table) {
            $table->dropColumn("base_record_type_id");
        });
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn("report_type_id");
        });
        Schema::table('rules', function (Blueprint $table) {
            $table->dropColumn("report_type_id");
        });
    }
}

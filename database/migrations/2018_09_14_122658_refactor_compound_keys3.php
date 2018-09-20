<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RefactorCompoundKeys3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // remove the legacy links by sid
        Schema::table('link_types', function (Blueprint $table) {
            $table->dropColumn("domain_sid");
            $table->dropColumn("range_sid");
        });
        Schema::table('links', function (Blueprint $table) {
            $table->dropColumn("subject_sid");
            $table->dropColumn("link_type_sid");
            $table->dropColumn("object_sid");
        });
        // record_type has no sid links
        Schema::table('records', function (Blueprint $table) {
            $table->dropColumn("record_type_sid");
        });
        Schema::table('report_types', function (Blueprint $table) {
            $table->dropColumn("base_record_type_sid");
        });
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn("report_type_sid");
        });
        Schema::table('rules', function (Blueprint $table) {
            $table->dropColumn("report_type_sid");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('link_types', function (Blueprint $table) {
            $table->integer('domain_sid')->unsigned();
            $table->integer('range_sid')->unsigned();
            $table->index('domain_sid');
            $table->index('range_sid');
        });
        Schema::table('links', function (Blueprint $table) {
            $table->integer('subject_sid')->unsigned();
            $table->integer('link_type_sid')->unsigned();
            $table->integer('object_sid')->unsigned();
            $table->index('subject_sid');
            $table->index('link_type_sid');
            $table->index('object_sid');
        });
        // record_type has no sid links
        Schema::table('records', function (Blueprint $table) {
            $table->integer('record_type_sid')->unsigned();
            $table->index('record_type_sid');
        });
        Schema::table('report_types', function (Blueprint $table) {
            $table->integer('base_record_type_sid')->unsigned();
            $table->index('base_record_type_sid');
        });
        Schema::table('reports', function (Blueprint $table) {
            $table->integer('report_type_sid')->unsigned();
            $table->index('report_type_sid');
        });
        Schema::table('rules', function (Blueprint $table) {
            $table->integer('report_type_sid')->unsigned();
            $table->index('report_type_sid');
        });
    }
}

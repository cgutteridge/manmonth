<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RefactorCompoundKeys2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // now populate the new _id links...
        $map = [];
        $map["reportType"] = $this->makeMap("report_types");
        $map["recordType"] = $this->makeMap("record_types");
        $map["record"] = $this->makeMap("records");
        $map["linkType"] = $this->makeMap("link_types");

        \App\Models\LinkType::chunk(1000, function ($link_types) use ($map) {
            foreach ($link_types as $link_type) {
                $link_type->domain_id = $map["recordType"][$link_type->document_revision_id][$link_type->domain_sid];
                $link_type->range_id = $map["recordType"][$link_type->document_revision_id][$link_type->range_sid];
                $link_type->save();
            }
        });

        \App\Models\Link::chunk(1000, function ($links) use ($map) {
            foreach ($links as $link) {

                if (!isset($map["record"][$link->document_revision_id])
                    || !isset($map["record"][$link->document_revision_id][$link->subject_sid])
                    || !isset($map["record"][$link->document_revision_id][$link->object_sid])) {
                    // clean up stray links
                    $link->delete();
                } else {
                    $link->subject_id = $map["record"][$link->document_revision_id][$link->subject_sid];
                    $link->object_id = $map["record"][$link->document_revision_id][$link->object_sid];
                    $link->link_type_id = $map["linkType"][$link->document_revision_id][$link->link_type_sid];
                    $link->save();
                }
            }
        });
        dump('records');
        \App\Models\Record::chunk(1000, function ($records) use ($map) {
            foreach ($records as $record) {
                dump('record chunk');
                $record->record_type_id = $map["recordType"][$record->document_revision_id][$record->record_type_sid];
                $record->save();
            }
        });

        \App\Models\ReportType::chunk(1000, function ($report_types) use ($map) {
            foreach ($report_types as $report_type) {
                dump('report-tpye chunk');

                $report_type->base_record_type_id = $map["recordType"][$report_type->document_revision_id][$report_type->base_record_type_sid];
                $report_type->save();
            }
        });

        \App\Models\Report::chunk(1000, function ($reports) use ($map) {
            foreach ($reports as $report) {
                dump('report chunk');

                $report->report_type_id = $map["reportType"][$report->document_revision_id][$report->report_type_sid];
                $report->save();
            }
        });

        \App\Models\Rule::chunk(1000, function ($rules) use ($map) {
            foreach ($rules as $rule) {
                dump('rule chunk');

                $rule->report_type_id = $map["reportType"][$rule->document_revision_id][$rule->report_type_sid];
                $rule->save();
            }
        });
    }

    function makeMap($table)
    {
        $rows = DB::table($table)->select('id', 'document_revision_id', 'sid')->get();
        $map = [];
        foreach ($rows as $row) {
            $map[$row->document_revision_id][$row->sid] = $row->id;
        }
        return $map;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public
    function down()
    {
        // no action
    }
}

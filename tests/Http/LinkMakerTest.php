<?php

/**
 * Created by PhpStorm.
 * User: cjg
 * Date: 04/10/2016
 * Time: 12:36
 */
class LinkMakerTest extends TestCase
{

    function test_Document_link()
    {
        $mock = new \App\Models\Document();
        $mock->id = 242;
        $linkMaker = new \App\Http\LinkMaker();
        $this->assertEquals('/documents/242', $linkMaker->link($mock));
    }

    function test_DocumentRevision_link()
    {
        $mock = new \App\Models\DocumentRevision();
        $mock->id = 242;
        $linkMaker = new \App\Http\LinkMaker();
        $this->assertEquals('/revisions/242', $linkMaker->link($mock));
    }

    function test_Record_link()
    {
        $mock = new \App\Models\Record();
        $mock->id = 242;
        $linkMaker = new \App\Http\LinkMaker();
        $this->assertEquals('/records/242', $linkMaker->link($mock));
    }

    function test_RecordType_link()
    {
        $mock = new \App\Models\RecordType();
        $mock->id = 242;
        $linkMaker = new \App\Http\LinkMaker();
        $this->assertEquals('/record-types/242', $linkMaker->link($mock));
    }

    function test_Link_link()
    {
        $mock = new \App\Models\Link();
        $mock->id = 242;
        $linkMaker = new \App\Http\LinkMaker();
        $this->assertEquals('/links/242', $linkMaker->link($mock));
    }

    function test_LinkType_link()
    {
        $mock = new \App\Models\LinkType();
        $mock->id = 242;
        $linkMaker = new \App\Http\LinkMaker();
        $this->assertEquals('/link-types/242', $linkMaker->link($mock));
    }

    function test_Report_link()
    {
        $mock = new \App\Models\Report();
        $mock->id = 242;
        $linkMaker = new \App\Http\LinkMaker();
        $this->assertEquals('/reports/242', $linkMaker->link($mock));
    }

    function test_Rule_link()
    {
        $mock = new \App\Models\Rule();
        $mock->id = 242;
        $linkMaker = new \App\Http\LinkMaker();
        $this->assertEquals('/rules/242', $linkMaker->link($mock));
    }

    function test_ReportType_link()
    {
        $mock = new \App\Models\ReportType();
        $mock->id = 242;
        $linkMaker = new \App\Http\LinkMaker();
        $this->assertEquals('/report-types/242', $linkMaker->link($mock));
    }

    function test_Record_edit_link()
    {
        $mock = new \App\Models\Record();
        $mock->id = 242;
        $linkMaker = new \App\Http\LinkMaker();
        $this->assertEquals('/records/242/edit', $linkMaker->edit($mock));
    }

    function test_Record_edit_link_with_params()
    {
        $mock = new \App\Models\Record();
        $mock->id = 242;
        $linkMaker = new \App\Http\LinkMaker();
        $this->assertEquals('/records/242/edit?foo=23&bar=24',
            $linkMaker->edit($mock, ["foo" => 23, "bar" => 24]));
    }

    function test_Record_link_with_params()
    {
        $mock = new \App\Models\Record();
        $mock->id = 242;
        $linkMaker = new \App\Http\LinkMaker();
        $this->assertEquals('/records/242?foo=23&bar=24',
            $linkMaker->link($mock, ["foo" => 23, "bar" => 24]));
    }

}
<?php

/**
 * Created by PhpStorm.
 * User: cjg
 * Date: 10/10/2016
 * Time: 23:37
 */
class TitleMakerTest extends TestCase
{

    function test_title_where_there_is_a_title()
    {
        $titleMaker = new \App\Http\TitleMaker();
        $field = App\Fields\Field::createFromData([
            "type" => "decimal",
            "label" => "dubdub",
            "name" => "test"
        ]);
        $this->assertEquals("dubdub", $titleMaker->title($field));
    }

    function test_title_where_there_is_no_title()
    {
        $titleMaker = new \App\Http\TitleMaker();
        $field = App\Fields\Field::createFromData([
            "type" => "decimal",
            "name" => "test"
        ]);
        $this->assertEquals("test", $titleMaker->title($field));
    }

    /*
     * does description belong outside the field model? yes....
        function test_field_description_where_there_is_a_description()
        {
            $linkMaker = new \App\Http\TitleMaker();
            $field = App\Fields\Field::createFromData([
                "type" => "decimal",
                "description" => "dubdub",
                "name" => "test"
            ]);
            $this->assertEquals("dubdub", $field->description());
        }

        function test_field_description_where_there_is_no_description()
        {
            $linkMaker = new \App\Http\TitleMaker();
            $field = App\Fields\Field::createFromData([
                "type" => "decimal",
                "name" => "test"
            ]);
            $this->assertNull($field->description());
        }
    */
}

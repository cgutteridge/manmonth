<?php

/**
 * Created by PhpStorm.
 * User: cjg
 * Date: 04/10/2016
 * Time: 12:01
 */
class MMScriptTest extends TestCase
{
    function makeMockDocRev()
    {
        $mockScript = $this->getMockBuilder(\App\Models\DocumentRevision::class)
            ->disableOriginalConstructor()->getMock();
        return $mockScript;
    }

    function test_string_literal()
    {
        $script = new \App\MMScript("'Hello'", $this->makeMockDocRev(), []);
        $this->assertEquals('string', $script->type());
        $this->assertEquals('Hello', $script->execute([])->value);
    }

    function test_decimal_literal()
    {
        $script = new \App\MMScript("3.14159", $this->makeMockDocRev(), []);
        $this->assertEquals('decimal', $script->type());
        $this->assertEquals(3.14159, $script->execute([])->value);
    }

    function test_integer_literal()
    {
        $script = new \App\MMScript("242", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $this->assertEquals(242, $script->execute([])->value);
    }

    function test_boolean_literal()
    {
        $script = new \App\MMScript("true", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(true, $script->execute([])->value);
    }

    function test_add_strings()
    {
        $script = new \App\MMScript("'foo'+'bar'", $this->makeMockDocRev(), []);
        $this->assertEquals('string', $script->type());
        $this->assertEquals('foobar', $script->execute([])->value);
    }

    function test_add_integers()
    {
        $script = new \App\MMScript("9+16", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $this->assertEquals(25, $script->execute([])->value);
    }

    function test_add_decimals()
    {
        $script = new \App\MMScript("9.1+16.1", $this->makeMockDocRev(), []);
        $this->assertEquals('decimal', $script->type());
        $this->assertEquals(25.2, $script->execute([])->value);
    }

    function test_subtract_decimals()
    {
        $script = new \App\MMScript("9.1-16.1", $this->makeMockDocRev(), []);
        $this->assertEquals('decimal', $script->type());
        $this->assertEquals(-7, $script->execute([])->value);
    }

    function test_subtract_integers()
    {
        $script = new \App\MMScript("9-16", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $this->assertEquals(-7, $script->execute([])->value);
    }

    function test_add_decimal_and_integer()
    {
        $script = new \App\MMScript("9.1+16", $this->makeMockDocRev(), []);
        $this->assertEquals('decimal', $script->type());
        $this->assertEquals(25.1, $script->execute([])->value);
    }

    function test_add_string_and_integer()
    {
        $script = new \App\MMScript("'foo'+606", $this->makeMockDocRev(), []);
        $this->assertEquals('string', $script->type());
        $this->assertEquals('foo606', $script->execute([])->value);
    }

    function test_multiply_integer_and_integer()
    {
        $script = new \App\MMScript("12*10", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $this->assertEquals(120, $script->execute([])->value);
    }

    function test_divide_integer_and_integer()
    {
        $script = new \App\MMScript("12/10", $this->makeMockDocRev(), []);
        $this->assertEquals('decimal', $script->type());
        $this->assertEquals(1.2, $script->execute([])->value);
    }


    function test_multiply_decimal_and_integer()
    {
        $script = new \App\MMScript("12*10.0", $this->makeMockDocRev(), []);
        $this->assertEquals('decimal', $script->type());
        $this->assertEquals(120, $script->execute([])->value);
    }

    function test_divide_decimal_and_integer()
    {
        $script = new \App\MMScript("12/10.0", $this->makeMockDocRev(), []);
        $this->assertEquals('decimal', $script->type());
        $this->assertEquals(1.2, $script->execute([])->value);
    }

    function test_divide_by_string_fails()
    {
        $this->setExpectedException(\App\Exceptions\ScriptException::class, "Can't DIV integer and string");
        new \App\MMScript("12/'fish'", $this->makeMockDocRev(), []);
    }

    function test_power_integer_and_integer()
    {
        $script = new \App\MMScript("5^3", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $this->assertEquals(125, $script->execute([])->value);
    }

    function test_power_decimal_and_integer()
    {
        $script = new \App\MMScript("1.1^2", $this->makeMockDocRev(), []);
        $this->assertEquals('decimal', $script->type());
        $this->assertEquals(1.21, $script->execute([])->value);
    }

    function test_power_to_string_fails()
    {
        $this->setExpectedException(\App\Exceptions\ScriptException::class, "Can't POW integer and string");
        new \App\MMScript("12^'fish'", $this->makeMockDocRev(), []);
    }
}
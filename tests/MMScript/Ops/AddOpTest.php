<?php

/**
 * Created by PhpStorm.
 * User: cjg
 * Date: 03/10/2016
 * Time: 14:35
 */
class AddOpTest extends TestCase
{
    function makeMockOp($type)
    {
        $mockOp = $this->getMockBuilder(\App\MMScript\Ops\Literal::class)->setMethods([
            "type"
        ])->disableOriginalConstructor()->getMock();
        $mockOp->expects($this->any())->method("type")->with()->willReturn($type);
        return $mockOp;
    }

    function makeMockScript()
    {
        $mockScript = $this->getMockBuilder(\App\MMScript::class)
            ->disableOriginalConstructor()->getMock();
        return $mockScript;
    }

    function test_type_will_return_int_given_int_and_int()
    {
        $left = $this->makeMockOp('integer');
        $right = $this->makeMockOp('integer');
        $script = $this->makeMockScript();
        $addOp = new \App\MMScript\Ops\AddOp(
            $script,
            [0, "NULL"],
            $left,
            $right);
        $this->assertEquals("integer", $addOp->type());
    }

    function test_type_will_return_dec_given_dec_and_int()
    {
        $left = $this->makeMockOp('decimal');
        $right = $this->makeMockOp('integer');
        $script = $this->makeMockScript();
        $addOp = new \App\MMScript\Ops\AddOp(
            $script,
            [0, "NULL"],
            $left,
            $right);
        $this->assertEquals("decimal", $addOp->type());
    }

    function test_type_will_return_dec_given_int_and_dec()
    {
        $left = $this->makeMockOp('integer');
        $right = $this->makeMockOp('decimal');
        $script = $this->makeMockScript();
        $addOp = new \App\MMScript\Ops\AddOp(
            $script,
            [0, "NULL"],
            $left,
            $right);
        $this->assertEquals("decimal", $addOp->type());
    }

    function test_type_will_throw_exception_given_minus_int_and_string()
    {
        $left = $this->makeMockOp('integer');
        $right = $this->makeMockOp('string');
        $script = $this->makeMockScript();
        $addOp = new \App\MMScript\Ops\AddOp(
            $script,
            [0, "MINUS"],
            $left,
            $right);
        $this->setExpectedException(\App\Exceptions\ScriptException::class);
        $addOp->type();
    }

    function test_type_will_return_string_given_plus_int_and_string()
    {
        $left = $this->makeMockOp('integer');
        $right = $this->makeMockOp('string');
        $script = $this->makeMockScript();
        $addOp = new \App\MMScript\Ops\AddOp(
            $script,
            [0, "PLUS"],
            $left,
            $right);
        $this->assertEquals("string", $addOp->type());
    }
}

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

    /* Tree Text */

    // this just walks the code but that's better than nothing, right?
    // TODO add link, name, call etc.
    function test_tree_text_code_kinda()
    {
        $script = new \App\MMScript("true & !false", $this->makeMockDocRev(), []);
        $script->textTree();
    }

    /* LITERAL */

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

    function test_negative_decimal_literal()
    {
        $script = new \App\MMScript("-3.14159", $this->makeMockDocRev(), []);
        $this->assertEquals('decimal', $script->type());
        $this->assertEquals(-3.14159, $script->execute([])->value);
    }

    function test_zero_lead_negative_decimal_literal()
    {
        $script = new \App\MMScript("-0.314159", $this->makeMockDocRev(), []);
        $this->assertEquals('decimal', $script->type());
        $this->assertEquals(-0.314159, $script->execute([])->value);
    }

    function test_negative_integer_literal()
    {
        $script = new \App\MMScript("-242", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $this->assertEquals(-242, $script->execute([])->value);
    }

    function test_boolean_literal()
    {
        $script = new \App\MMScript("true", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(true, $script->execute([])->value);
    }

    function test_bogus_literal()
    {
        $literal = new \App\MMScript\Ops\Literal(null, [0, "FISH", true]);
        $this->setExpectedException(\App\Exceptions\ScriptException::class);
        $literal->type();
    }


    /* ADD */

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

    function test_adding_booleans_should_fail_in_runtime()
    {
        $addOp = new \App\MMScript\Ops\AddOp(
            null,
            [0, "ADD"],
            new \App\MMScript\Ops\Literal(null, [0, "BOOL", true]),
            new \App\MMScript\Ops\Literal(null, [0, "BOOL", true]));
        $this->setExpectedException(\App\Exceptions\MMScriptRuntimeException::class);
        $addOp->execute([]);
    }

    /* MULTIPLY */

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

    function test_divide_by_zero_fails()
    {
        $this->setExpectedException(\App\Exceptions\ScriptException::class);
        $script = new \App\MMScript("12/0", $this->makeMockDocRev(), []);
        $script->execute([]);
    }


    /* POWER */

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

    /* AND */

    function test_and_with_true_and_true()
    {
        $script = new \App\MMScript("true & true", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(true, $script->execute([])->value);
    }

    function test_and_with_true_and_false()
    {
        $script = new \App\MMScript("true & false", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(false, $script->execute([])->value);
    }

    function test_and_with_false_and_false()
    {
        $script = new \App\MMScript("false & false", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(false, $script->execute([])->value);
    }

    function test_and_throws_exception_with_true_and_string()
    {
        $this->setExpectedException(\App\Exceptions\ScriptException::class);
        new \App\MMScript("true & 'fish'", $this->makeMockDocRev(), []);
    }

    function test_and_throws_exception_with_integer_and_false()
    {
        $this->setExpectedException(\App\Exceptions\ScriptException::class);
        new \App\MMScript("23 & false", $this->makeMockDocRev(), []);
    }

    /* OR */

    function test_or_with_true_and_true()
    {
        $script = new \App\MMScript("true | true", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(true, $script->execute([])->value);
    }

    function test_or_with_true_and_false()
    {
        $script = new \App\MMScript("true | false", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(true, $script->execute([])->value);
    }

    function test_or_with_false_and_false()
    {
        $script = new \App\MMScript("false | false", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(false, $script->execute([])->value);
    }

    function test_or_throws_exception_with_true_and_string()
    {
        $this->setExpectedException(\App\Exceptions\ScriptException::class);
        new \App\MMScript("true | 'fish'", $this->makeMockDocRev(), []);
    }

    function test_or_throws_exception_with_integer_and_false()
    {
        $this->setExpectedException(\App\Exceptions\ScriptException::class);
        new \App\MMScript("23 | false", $this->makeMockDocRev(), []);
    }

    /* NOT */

    function test_not_true_is_false()
    {
        $script = new \App\MMScript("! true", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(false, $script->execute([])->value);
    }

    function test_not_false_is_true()
    {
        $script = new \App\MMScript("! false", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(true, $script->execute([])->value);
    }

    function test_not_integer_throws_exception()
    {
        $this->setExpectedException(\App\Exceptions\ScriptException::class);
        new \App\MMScript("! 23", $this->makeMockDocRev(), []);
    }

    function test_double_not_true_is_true()
    {
        $script = new \App\MMScript("!! true", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(true, $script->execute([])->value);
    }

    /* CMP */

    function test_cmp_strings()
    {
        $script = new \App\MMScript("'foo'='bar'", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
    }

    function test_cmp_integers()
    {
        $script = new \App\MMScript("9 = 16", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
    }

    function test_cmp_decimals()
    {
        $script = new \App\MMScript("9.1=6.1", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
    }

    function test_string_comparison_equals_is_true()
    {
        $script = new \App\MMScript("'foo'='foo'", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(true, $script->execute([])->value);
    }

    function test_string_comparison_equals_is_false()
    {
        $script = new \App\MMScript("'foo'='bar'", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(false, $script->execute([])->value);
    }

    function test_string_comparison_unequal_is_true()
    {
        $script = new \App\MMScript("'foo'<>'bar'", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(true, $script->execute([])->value);
    }

    function test_string_comparison_unequal_is_false()
    {
        $script = new \App\MMScript("'foo'<>'foo'", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(false, $script->execute([])->value);
    }

    function test_string_comparison_less_or_equals_is_true()
    {
        $script = new \App\MMScript("'bar'<='foo'", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(true, $script->execute([])->value);
    }

    function test_string_comparison_less_or_equals_is_false()
    {
        $script = new \App\MMScript("'foo'<='bar'", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(false, $script->execute([])->value);
    }

    function test_string_comparison_greater_or_equals_is_true()
    {
        $script = new \App\MMScript("'foo'>='bar'", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(true, $script->execute([])->value);
    }

    function test_string_comparison_greater_or_equals_is_false()
    {
        $script = new \App\MMScript("'bar'>='foo'", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(false, $script->execute([])->value);
    }

    function test_string_comparison_less_is_true()
    {
        $script = new \App\MMScript("'bar'<'foo'", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(true, $script->execute([])->value);
    }

    function test_string_comparison_less_is_false()
    {
        $script = new \App\MMScript("'foo'<'bar'", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(false, $script->execute([])->value);
    }

    function test_string_comparison_greater_is_true()
    {
        $script = new \App\MMScript("'foo'>'bar'", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(true, $script->execute([])->value);
    }

    function test_string_comparison_greater_is_false()
    {
        $script = new \App\MMScript("'bar'>'foo'", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(false, $script->execute([])->value);
    }


    function test_decimal_comparison_equals_is_true()
    {
        $script = new \App\MMScript("23.1=23.1", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(true, $script->execute([])->value);
    }

    function test_decimal_comparison_equals_is_false()
    {
        $script = new \App\MMScript("23.1=23", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(false, $script->execute([])->value);
    }

    function test_decimal_comparison_unequal_is_true()
    {
        $script = new \App\MMScript("23.1<>1969.1", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(true, $script->execute([])->value);
    }

    function test_decimal_comparison_unequal_is_false()
    {
        $script = new \App\MMScript("187<>187", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(false, $script->execute([])->value);
    }

    function test_decimal_comparison_less_or_equals_is_true()
    {
        $script = new \App\MMScript("2<=2", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(true, $script->execute([])->value);
    }

    function test_decimal_comparison_less_or_equals_is_false()
    {
        $script = new \App\MMScript("2<=1.7", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(false, $script->execute([])->value);
    }

    function test_decimal_comparison_greater_or_equals_is_true()
    {
        $script = new \App\MMScript("999.99>=123.1", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(true, $script->execute([])->value);
    }

    function test_decimal_comparison_greater_or_equals_is_false()
    {
        $script = new \App\MMScript("99>=-1.11111", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(true, $script->execute([])->value);
    }

    function test_decimal_comparison_less_is_true()
    {
        $script = new \App\MMScript("23<24.9", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(true, $script->execute([])->value);
    }

    function test_decimal_comparison_less_is_false()
    {
        $script = new \App\MMScript("99.1<99", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(false, $script->execute([])->value);
    }

    function test_decimal_comparison_greater_is_true()
    {
        $script = new \App\MMScript("99.1>89.9", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(true, $script->execute([])->value);
    }

    function test_decimal_comparison_greater_is_false()
    {
        $script = new \App\MMScript("99>100", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $this->assertEquals(false, $script->execute([])->value);
    }

    function test_comparing_string_and_boolean_fails()
    {
        $this->setExpectedException(\App\Exceptions\ScriptException::class);
        new \App\MMScript("'fish'>true", $this->makeMockDocRev(), []);
    }

    /* UNARY MINUS */

    function test_unary_minus()
    {
        $script = new \App\MMScript("-242", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $this->assertEquals(-242, $script->execute([])->value);
    }

    function test_unary_minus_on_boolean_fails()
    {
        $this->setExpectedException(\App\Exceptions\ScriptException::class);
        new \App\MMScript("-true", $this->makeMockDocRev(), []);
    }

    function test_unary_minus_on_string_fails()
    {
        $this->setExpectedException(\App\Exceptions\ScriptException::class);
        new \App\MMScript("-'fish'", $this->makeMockDocRev(), []);
    }

    function test_double_unary_minus()
    {
        $script = new \App\MMScript("--242", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $this->assertEquals(242, $script->execute([])->value);
    }

}
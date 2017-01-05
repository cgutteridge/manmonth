<?php
use App\Models\DocumentRevision;

/**
 * Created by PhpStorm.
 * User: cjg
 * Date: 04/10/2016
 * Time: 12:01
 * Tests the MMScript Functions
 */
class MMScriptFuncsTest extends TestCase
{

    function test_non_existent_function_fails()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("fakefunction('2','3')", $this->makeMockDocRev(), []);
        $script->type();
    }

    function makeMockDocRev()
    {
        $mockScript = $this->getMockBuilder(DocumentRevision::class)
            ->disableOriginalConstructor()->getMock();
        return $mockScript;
    }

    // CastString */

    function test_cast_string_to_string()
    {
        $script = new \App\MMScript("string('Hello')", $this->makeMockDocRev(), []);
        $this->assertEquals('string', $script->type());
        $this->assertEquals('Hello', $script->execute([])->value);
    }


    function test_cast_int_to_string()
    {
        $script = new \App\MMScript("string(23)", $this->makeMockDocRev(), []);
        $this->assertEquals('string', $script->type());
        $this->assertEquals('23', $script->execute([])->value);
    }


    function test_cast_decimal_to_string()
    {
        $script = new \App\MMScript("string(-123.4)", $this->makeMockDocRev(), []);
        $this->assertEquals('string', $script->type());
        $this->assertEquals('-123.4', $script->execute([])->value);
    }

    function test_cast_negative_decimal_to_string()
    {
        $script = new \App\MMScript("string(-1.123)", $this->makeMockDocRev(), []);
        $this->assertEquals('string', $script->type());
        $this->assertEquals('-1.123', $script->execute([])->value);
    }

    function test_cast_decimal_with_trailing_zeros_to_string()
    {
        $script = new \App\MMScript("string(0.1230000)", $this->makeMockDocRev(), []);
        $this->assertEquals('string', $script->type());
        $this->assertEquals('0.123', $script->execute([])->value);
    }

    function test_cast_boolean_true_to_string()
    {
        $script = new \App\MMScript("string(true)", $this->makeMockDocRev(), []);
        $this->assertEquals('string', $script->type());
        $this->assertEquals('TRUE', $script->execute([])->value);
    }

    function test_cast_boolean_false_to_string()
    {
        $script = new \App\MMScript("string(false)", $this->makeMockDocRev(), []);
        $this->assertEquals('string', $script->type());
        $this->assertEquals('FALSE', $script->execute([])->value);
    }

    function test_cast_null_to_string()
    {
        $script = new \App\MMScript("string(null)", $this->makeMockDocRev(), []);
        $this->assertEquals('string', $script->type());
        $result = $script->execute([]);
        $this->assertEquals('NULL', $result->value);
    }

    function test_cast_to_string_fails_with_no_parameters()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("string()", $this->makeMockDocRev(), []);
        $script->type();
    }

    function test_cast_to_string_fails_with_extra_parameter()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("string(2,3)", $this->makeMockDocRev(), []);
        $script->type();
    }


    /* CastDecimal */

    function test_cast_postive_int_string_to_decimal()
    {
        $script = new \App\MMScript("decimal('103')", $this->makeMockDocRev(), []);
        $this->assertEquals('decimal', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\DecimalValue::class, $result);
        $this->assertEquals(103, $result->value);
    }

    function test_cast_negative_int_string_to_decimal()
    {
        $script = new \App\MMScript("decimal('-103')", $this->makeMockDocRev(), []);
        $this->assertEquals('decimal', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\DecimalValue::class, $result);
        $this->assertEquals(-103, $result->value);
    }

    function test_cast_postive_decimal_string_to_decimal()
    {
        $script = new \App\MMScript("decimal('103.12')", $this->makeMockDocRev(), []);
        $this->assertEquals('decimal', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\DecimalValue::class, $result);
        $this->assertEquals(103.12, $result->value);
    }

    function test_cast_negative_decimal_string_to_decimal()
    {
        $script = new \App\MMScript("decimal('-103.12')", $this->makeMockDocRev(), []);
        $this->assertEquals('decimal', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\DecimalValue::class, $result);
        $this->assertEquals(-103.12, $result->value);
    }

    function test_cast_decimal_string_with_leading_zeros_to_decimal()
    {
        $script = new \App\MMScript("decimal('00000103.12')", $this->makeMockDocRev(), []);
        $this->assertEquals('decimal', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\DecimalValue::class, $result);
        $this->assertEquals(103.12, $result->value);
    }

    function test_cast_decimal_string_with_trailing_junk_to_decimal()
    {
        $script = new \App\MMScript("decimal('103.13junk66')", $this->makeMockDocRev(), []);
        $this->assertEquals('decimal', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\DecimalValue::class, $result);
        $this->assertEquals(103.13, $result->value);
    }

    function test_cast_non_decimal_string_to_decimal()
    {
        $script = new \App\MMScript("decimal('fish')", $this->makeMockDocRev(), []);
        $this->assertEquals('decimal', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\DecimalValue::class, $result);
        $this->assertEquals(0, $result->value);
    }

    function test_cast_empty_string_to_decimal()
    {
        $script = new \App\MMScript("decimal('')", $this->makeMockDocRev(), []);
        $this->assertEquals('decimal', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\DecimalValue::class, $result);
        $this->assertEquals(0, $result->value);
    }

    function test_cast_int_to_decimal()
    {
        $script = new \App\MMScript("decimal(23)", $this->makeMockDocRev(), []);
        $this->assertEquals('decimal', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\DecimalValue::class, $result);
        $this->assertEquals(23, $result->value);
    }

    function test_cast_decimal_to_decimal()
    {
        $script = new \App\MMScript("decimal(23.1)", $this->makeMockDocRev(), []);
        $this->assertEquals('decimal', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\DecimalValue::class, $result);
        $this->assertEquals(23.1, $result->value);
    }

    function test_cast_boolean_true_to_decimal()
    {
        $script = new \App\MMScript("decimal(true)", $this->makeMockDocRev(), []);
        $this->assertEquals('decimal', $script->type());
        $this->assertEquals(1, $script->execute([])->value);
    }

    function test_cast_boolean_false_to_decimal()
    {
        $script = new \App\MMScript("decimal(false)", $this->makeMockDocRev(), []);
        $this->assertEquals('decimal', $script->type());
        $this->assertEquals(0, $script->execute([])->value);
    }

    function test_cast_null_to_decimal()
    {
        $script = new \App\MMScript("decimal(null)", $this->makeMockDocRev(), []);
        $this->assertEquals('decimal', $script->type());
        $result = $script->execute([]);
        $this->assertEquals(0, $result->value);
    }

    function test_cast_to_decimal_fails_with_no_parameters()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("decimal()", $this->makeMockDocRev(), []);
        $script->type();
    }

    function test_cast_to_decimal_fails_with_extra_parameter()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("decimal('2','3')", $this->makeMockDocRev(), []);
        $script->type();
    }

    /* Ceil */

    function test_ceil_fails_with_no_parameters()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("ceil()", $this->makeMockDocRev(), []);
        $script->type();
    }

    function test_ceil_fails_with_extra_parameter()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("ceil(2.4,3.5)", $this->makeMockDocRev(), []);
        $script->type();
    }

    function test_ceil_fails_with_boolean()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("ceil(true)", $this->makeMockDocRev(), []);
        $script->type();
    }

    function test_ceil_fails_with_string()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("ceil('1.23')", $this->makeMockDocRev(), []);
        $script->type();
    }

    function test_ceil_fails_with_null()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("ceil(null)", $this->makeMockDocRev(), []);
        $script->type();
    }

    function test_ceil_with_zero()
    {
        $script = new \App\MMScript("ceil(0)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(0, $result->value);
    }

    function test_ceil_with_positive_int()
    {
        $script = new \App\MMScript("ceil(12)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(12, $result->value);
    }

    function test_ceil_with_negative_int()
    {
        $script = new \App\MMScript("ceil(-13)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(-13, $result->value);
    }

    function test_ceil_with_positive_rounded_decimal()
    {
        $script = new \App\MMScript("ceil(14.0)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(14, $result->value);
    }

    function test_ceil_with_positive_low_decimal()
    {
        $script = new \App\MMScript("ceil(14.1)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(15, $result->value);
    }

    function test_ceil_with_positive_mid_decimal()
    {
        $script = new \App\MMScript("ceil(14.5)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(15, $result->value);
    }

    function test_ceil_with_positive_high_decimal()
    {
        $script = new \App\MMScript("ceil(14.9)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(15, $result->value);
    }

    function test_ceil_with_negative_rounded_decimal()
    {
        $script = new \App\MMScript("ceil(-14.0)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(-14, $result->value);
    }

    function test_ceil_with_negative_low_decimal()
    {
        $script = new \App\MMScript("ceil(-14.1)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(-14, $result->value);
    }

    function test_ceil_with_negative_mid_decimal()
    {
        $script = new \App\MMScript("ceil(-14.5)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(-14, $result->value);
    }

    function test_ceil_with_negative_high_decimal()
    {
        $script = new \App\MMScript("ceil(-14.9)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(-14, $result->value);
    }

    /* Floor */

    function test_floor_fails_with_no_parameters()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("floor()", $this->makeMockDocRev(), []);
        $script->type();
    }

    function test_floor_fails_with_extra_parameter()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("floor(2.4,3.5)", $this->makeMockDocRev(), []);
        $script->type();
    }

    function test_floor_fails_with_boolean()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("floor(true)", $this->makeMockDocRev(), []);
        $script->type();
    }

    function test_floor_fails_with_string()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("floor('1.23')", $this->makeMockDocRev(), []);
        $script->type();
    }

    function test_floor_fails_with_null()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("floor(null)", $this->makeMockDocRev(), []);
        $script->type();
    }

    function test_floor_with_zero()
    {
        $script = new \App\MMScript("floor(0)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(0, $result->value);
    }

    function test_floor_with_positive_int()
    {
        $script = new \App\MMScript("floor(12)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(12, $result->value);
    }

    function test_floor_with_negative_int()
    {
        $script = new \App\MMScript("floor(-13)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(-13, $result->value);
    }

    function test_floor_with_positive_rounded_decimal()
    {
        $script = new \App\MMScript("floor(14.0)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(14, $result->value);
    }

    function test_floor_with_positive_low_decimal()
    {
        $script = new \App\MMScript("floor(14.1)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(14, $result->value);
    }

    function test_floor_with_positive_mid_decimal()
    {
        $script = new \App\MMScript("floor(14.5)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(14, $result->value);
    }

    function test_floor_with_positive_high_decimal()
    {
        $script = new \App\MMScript("floor(14.9)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(14, $result->value);
    }

    function test_floor_with_negative_rounded_decimal()
    {
        $script = new \App\MMScript("floor(-14.0)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(-14, $result->value);
    }

    function test_floor_with_negative_low_decimal()
    {
        $script = new \App\MMScript("floor(-14.1)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(-15, $result->value);
    }

    function test_floor_with_negative_mid_decimal()
    {
        $script = new \App\MMScript("floor(-14.5)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(-15, $result->value);
    }

    function test_floor_with_negative_high_decimal()
    {
        $script = new \App\MMScript("floor(-14.9)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(-15, $result->value);
    }

    /* Round */

    function test_round_fails_with_no_parameters()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("round()", $this->makeMockDocRev(), []);
        $script->type();
    }

    function test_round_fails_with_extra_parameter()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("round(2.4,3.5)", $this->makeMockDocRev(), []);
        $script->type();
    }

    function test_round_fails_with_boolean()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("round(true)", $this->makeMockDocRev(), []);
        $script->type();
    }

    function test_round_fails_with_string()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("round('1.23')", $this->makeMockDocRev(), []);
        $script->type();
    }

    function test_round_fails_with_null()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("round(null)", $this->makeMockDocRev(), []);
        $script->type();
    }

    function test_round_with_zero()
    {
        $script = new \App\MMScript("round(0)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(0, $result->value);
    }

    function test_round_with_positive_int()
    {
        $script = new \App\MMScript("round(12)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(12, $result->value);
    }

    function test_round_with_negative_int()
    {
        $script = new \App\MMScript("round(-13)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(-13, $result->value);
    }

    function test_round_with_positive_rounded_decimal()
    {
        $script = new \App\MMScript("round(14.0)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(14, $result->value);
    }

    function test_round_with_positive_low_decimal()
    {
        $script = new \App\MMScript("round(14.1)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(14, $result->value);
    }

    function test_round_with_positive_mid_decimal()
    {
        $script = new \App\MMScript("round(14.5)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(15, $result->value);
    }

    function test_round_with_positive_high_decimal()
    {
        $script = new \App\MMScript("round(14.9)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(15, $result->value);
    }

    function test_round_with_negative_rounded_decimal()
    {
        $script = new \App\MMScript("round(-14.0)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(-14, $result->value);
    }

    function test_round_with_negative_low_decimal()
    {
        $script = new \App\MMScript("round(-14.1)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(-14, $result->value);
    }

    function test_round_with_negative_mid_decimal()
    {
        $script = new \App\MMScript("round(-14.5)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(-15, $result->value);
    }

    function test_round_with_negative_high_decimal()
    {
        $script = new \App\MMScript("round(-14.9)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(-15, $result->value);
    }

    /* Max */

    function test_max_fails_with_no_parameters()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("max()", $this->makeMockDocRev(), []);
        $script->type();
    }

    function test_max_fails_with_string()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("max('junk')", $this->makeMockDocRev(), []);
        $script->type();
    }

    function test_max_fails_with_boolean_and_decimal()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("max(true,23)", $this->makeMockDocRev(), []);
        $script->type();
    }

    function test_max_fails_with_decimal_and_boolean()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("max(23,true)", $this->makeMockDocRev(), []);
        $script->type();
    }

    function test_max_with_int_and_int()
    {
        $script = new \App\MMScript("max(11,10)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(11, $result->value);
    }

    function test_max_with_dec_and_int()
    {
        $script = new \App\MMScript("max(11.1,12)", $this->makeMockDocRev(), []);
        $this->assertEquals('decimal', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\DecimalValue::class, $result);
        $this->assertEquals(12, $result->value);
    }

    function test_max_with_int_and_dec()
    {
        $script = new \App\MMScript("max(13,11.1)", $this->makeMockDocRev(), []);
        $this->assertEquals('decimal', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\DecimalValue::class, $result);
        $this->assertEquals(13, $result->value);
    }

    function test_max_with_lots_of_parameters()
    {
        $script = new \App\MMScript("max(-101.1,11.1,10,99,1,9999,1234,4,-4,0)", $this->makeMockDocRev(), []);
        $this->assertEquals('decimal', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\DecimalValue::class, $result);
        $this->assertEquals(9999, $result->value);
    }


    /* Min */

    function test_min_fails_with_no_parameters()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("min()", $this->makeMockDocRev(), []);
        $script->type();
    }

    function test_min_fails_with_string()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("min('junk')", $this->makeMockDocRev(), []);
        $script->type();
    }

    function test_min_fails_with_boolean_and_decimal()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("min(true,23)", $this->makeMockDocRev(), []);
        $script->type();
    }

    function test_min_fails_with_decimal_and_boolean()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("min(23,true)", $this->makeMockDocRev(), []);
        $script->type();
    }

    function test_min_with_int_and_int()
    {
        $script = new \App\MMScript("min(11,10)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(10, $result->value);
    }

    function test_min_with_dec_and_int()
    {
        $script = new \App\MMScript("min(11.1,12)", $this->makeMockDocRev(), []);
        $this->assertEquals('decimal', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\DecimalValue::class, $result);
        $this->assertEquals(11.1, $result->value);
    }

    function test_min_with_int_and_dec()
    {
        $script = new \App\MMScript("min(13,11.1)", $this->makeMockDocRev(), []);
        $this->assertEquals('decimal', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\DecimalValue::class, $result);
        $this->assertEquals(11.1, $result->value);
    }

    function test_min_with_lots_of_parameters()
    {
        $script = new \App\MMScript("min(-101.1,11.1,10,99,1,9999,1234,4,-4,0)", $this->makeMockDocRev(), []);
        $this->assertEquals('decimal', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\DecimalValue::class, $result);
        $this->assertEquals(-101.1, $result->value);
    }

    /* IF */

    function test_if_fails_with_no_parameters()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("if()", $this->makeMockDocRev(), []);
        $script->type();
    }

    function test_if_fails_if_first_param_is_not_boolean()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("if('junk','yay')", $this->makeMockDocRev(), []);
        $script->type();
    }

    function test_if_fails_with_four_parameters()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("if(true,'yay','boo','eh?')", $this->makeMockDocRev(), []);
        $script->type();
    }

    function test_if_fails_with_bool_string_int()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("if(true,'yay',23)", $this->makeMockDocRev(), []);
        $script->type();
    }

    function test_if_fails_with_bool_decimal_int()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("if(true,23.0,23)", $this->makeMockDocRev(), []);
        $script->type();
    }

    function test_if_fails_with_bool_string_bool()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("if(true,'fish',true)", $this->makeMockDocRev(), []);
        $script->type();
    }

    function test_if_fails_with_bool_string_null()
    {
        $this->setExpectedException(\App\Exceptions\CallException::class);
        $script = new \App\MMScript("if(true,'fish',null)", $this->makeMockDocRev(), []);
        $script->type();
    }

    function test_if_with_true_string()
    {
        $script = new \App\MMScript("if(true,'yay','meh')", $this->makeMockDocRev(), []);
        $this->assertEquals('string', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\StringValue::class, $result);
        $this->assertEquals('yay', $result->value);
    }

    function test_if_with_false_string()
    {
        $script = new \App\MMScript("if(false,'yay','meh')", $this->makeMockDocRev(), []);
        $this->assertEquals('string', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\StringValue::class, $result);
        $this->assertEquals('meh', $result->value);
    }

    function test_if_with_true_decimal()
    {
        $script = new \App\MMScript("if(true,23.17,42.5)", $this->makeMockDocRev(), []);
        $this->assertEquals('decimal', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\DecimalValue::class, $result);
        $this->assertEquals(23.17, $result->value);
    }

    function test_if_with_false_decimal()
    {
        $script = new \App\MMScript("if(false,23.17,42.5)", $this->makeMockDocRev(), []);
        $this->assertEquals('decimal', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\DecimalValue::class, $result);
        $this->assertEquals(42.5, $result->value);
    }

    function test_if_with_true_integer()
    {
        $script = new \App\MMScript("if(true,23,42)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(23, $result->value);
    }

    function test_if_with_false_integer()
    {
        $script = new \App\MMScript("if(false,23,42)", $this->makeMockDocRev(), []);
        $this->assertEquals('integer', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $result);
        $this->assertEquals(42, $result->value);
    }

    /* isset */

    function test_isset_with_false()
    {
        $script = new \App\MMScript("isset(false)", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\BooleanValue::class, $result);
        $this->assertEquals(true, $result->value);
    }


    function test_isset_with_true()
    {
        $script = new \App\MMScript("isset(true)", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\BooleanValue::class, $result);
        $this->assertEquals(true, $result->value);
    }

    function test_isset_with_zero()
    {
        $script = new \App\MMScript("isset(0)", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\BooleanValue::class, $result);
        $this->assertEquals(true, $result->value);
    }

    function test_isset_with_empty_string()
    {
        $script = new \App\MMScript("isset('')", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\BooleanValue::class, $result);
        $this->assertEquals(true, $result->value);
    }

    function test_isset_with_filled_string()
    {
        $script = new \App\MMScript("isset('junk')", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\BooleanValue::class, $result);
        $this->assertEquals(true, $result->value);
    }

    function test_isset_with_null_is_false()
    {
        $script = new \App\MMScript("isset(null)", $this->makeMockDocRev(), []);
        $this->assertEquals('boolean', $script->type());
        $result = $script->execute([]);
        $this->assertInstanceOf(\App\MMScript\Values\BooleanValue::class, $result);
        $this->assertEquals(false, $result->value);
    }


}
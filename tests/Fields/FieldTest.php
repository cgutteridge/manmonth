<?php

/**
 * Created by PhpStorm.
 * User: cjg
 * Date: 04/10/2016
 * Time: 16:06
 */
class FieldTest extends TestCase
{

    function test_boolean_validation_code()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "boolean",
            "name" => "test"
        ]);
        $this->assertEquals("boolean", $field->valueValidationCode());
    }

    function test_string_validation_code()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "string",
            "name" => "test"
        ]);
        $this->assertEquals("string", $field->valueValidationCode());
    }

    function test_integer_validation_code()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "integer",
            "name" => "test"
        ]);
        $this->assertEquals("integer", $field->valueValidationCode());
    }

    function test_decimal_validation_code()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "decimal",
            "name" => "test"
        ]);
        $this->assertEquals("numeric", $field->valueValidationCode());
    }

    function test_decimal_with_min_validation_code()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "decimal",
            "min" => 303,
            "name" => "test"
        ]);
        $this->assertEquals("min:303|numeric", $field->valueValidationCode());
    }

    function test_decimal_with_required_min_and_max_validation_code()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "decimal",
            "min" => 303,
            "max" => 404,
            "required" => true,
            "name" => "test"
        ]);
        $this->assertEquals("max:404|min:303|numeric|required", $field->valueValidationCode());
    }

    function test_integer_with_min_validation_code()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "integer",
            "min" => 303,
            "name" => "test"
        ]);
        $this->assertEquals("integer|min:303", $field->valueValidationCode());
    }

    function test_integer_with_required_min_and_max_validation_code()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "integer",
            "min" => 303,
            "max" => 404,
            "required" => true,
            "name" => "test"
        ]);
        $this->assertEquals("integer|max:404|min:303|required", $field->valueValidationCode());
    }

    function test_required_in_validation_code()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "decimal",
            "required" => true,
            "name" => "test"
        ]);
        $this->assertEquals("numeric|required", $field->valueValidationCode());
    }

    function test_bad_field_type_throws_exception()
    {
        $this->setExpectedException("Exception", "Unknown field type: 'penguin'");
        App\Fields\Field::createFromData(["type" => "penguin"]);
    }

    function test_required_method_when_field_is_required()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "decimal",
            "required" => true,
            "name" => "test"
        ]);
        $this->assertEquals(true, $field->required());
    }

    function test_required_method_when_field_is_not_required()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "decimal",
            "name" => "test"
        ]);
        $this->assertEquals(false, $field->required());
    }

    function test_boolean_field_validation_array()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "boolean",
            "name" => "test"
        ]);
        $this->assertEquals(
            [
                'name' => 'required|codename|min:2|max:255',
                'label' => 'string',
                'description' => 'string',
                'required' => 'boolean',
                'type' => 'required|in:boolean',
                'default' => 'boolean',
                'mode' => 'string|in:prefer_local,prefer_external,only_local,only_external',
                'script' => 'string'
            ],
            $field->fieldValidationArray());
    }

    function test_string_field_validation_array()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "string",
            "name" => "test"
        ]);
        $this->assertEquals(
            [
                'name' => 'required|codename|min:2|max:255',
                'label' => 'string',
                'description' => 'string',
                'required' => 'boolean',
                'type' => 'required|in:string',
                'default' => 'string',
                'mode' => 'string|in:prefer_local,prefer_external,only_local,only_external',
                'script' => 'string'
            ],
            $field->fieldValidationArray());
    }

    function test_integer_field_validation_array()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "integer",
            "name" => "test"
        ]);
        $this->assertEquals(
            [
                'name' => 'required|codename|min:2|max:255',
                'label' => 'string',
                'description' => 'string',
                'required' => 'boolean',
                'type' => 'required|in:integer',
                'default' => 'integer',
                'mode' => 'string|in:prefer_local,prefer_external,only_local,only_external',
                'script' => 'string'
            ],
            $field->fieldValidationArray());
    }

    function test_decimal_field_validation_array()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "decimal",
            "name" => "test"
        ]);
        $this->assertEquals(
            [
                'name' => 'required|codename|min:2|max:255',
                'label' => 'string',
                'description' => 'string',
                'required' => 'boolean',
                'type' => 'required|in:decimal',
                'default' => 'numeric',
                'mode' => 'string|in:prefer_local,prefer_external,only_local,only_external',
                'script' => 'string'
            ],
            $field->fieldValidationArray());
    }

    function test_make_boolean_value()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "boolean",
            "name" => "test"
        ]);
        $value = $field->makeValue(true);
        $this->assertInstanceOf(\App\MMScript\Values\BooleanValue::class, $value);
        $this->assertEquals(true, $value->value);
    }

    function test_make_null_boolean_value()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "boolean",
            "name" => "test"
        ]);
        $value = $field->makeValue(null);
        $this->assertInstanceOf(\App\MMScript\Values\NullValue::class, $value);
        $this->assertNull($value->value);
    }

    function test_make_default_boolean_value()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "boolean",
            "name" => "test",
            "default" => false
        ]);
        $value = $field->makeValue(null);
        $this->assertInstanceOf(\App\MMScript\Values\BooleanValue::class, $value);
        $this->assertEquals(false, $value->value);
    }


    function test_make_string_value()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "string",
            "name" => "test"
        ]);
        $value = $field->makeValue(true);
        $this->assertInstanceOf(\App\MMScript\Values\StringValue::class, $value);
        $this->assertEquals(true, $value->value);
    }

    function test_make_null_string_value()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "string",
            "name" => "test"
        ]);
        $value = $field->makeValue(null);
        $this->assertInstanceOf(\App\MMScript\Values\NullValue::class, $value);
        $this->assertNull($value->value);
    }

    function test_make_default_string_value()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "string",
            "name" => "test",
            "default" => 'fish'
        ]);
        $value = $field->makeValue(null);
        $this->assertInstanceOf(\App\MMScript\Values\StringValue::class, $value);
        $this->assertEquals('fish', $value->value);
    }


    function test_make_integer_value()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "integer",
            "name" => "test"
        ]);
        $value = $field->makeValue(true);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $value);
        $this->assertEquals(true, $value->value);
    }

    function test_make_null_integer_value()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "integer",
            "name" => "test"
        ]);
        $value = $field->makeValue(null);
        $this->assertInstanceOf(\App\MMScript\Values\NullValue::class, $value);
        $this->assertNull($value->value);
    }

    function test_make_default_integer_value()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "integer",
            "name" => "test",
            "default" => 242
        ]);
        $value = $field->makeValue(null);
        $this->assertInstanceOf(\App\MMScript\Values\IntegerValue::class, $value);
        $this->assertEquals(242, $value->value);
    }


    function test_make_decimal_value()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "decimal",
            "name" => "test"
        ]);
        $value = $field->makeValue(true);
        $this->assertInstanceOf(\App\MMScript\Values\DecimalValue::class, $value);
        $this->assertEquals(true, $value->value);
    }

    function test_make_null_decimal_value()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "decimal",
            "name" => "test"
        ]);
        $value = $field->makeValue(null);
        $this->assertInstanceOf(\App\MMScript\Values\NullValue::class, $value);
        $this->assertNull($value->value);
    }

    function test_make_default_decimal_value()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "decimal",
            "name" => "test",
            "default" => 23.4
        ]);
        $value = $field->makeValue(null);
        $this->assertInstanceOf(\App\MMScript\Values\DecimalValue::class, $value);
        $this->assertEquals(23.4, $value->value);
    }

    function test_self_validation_when_valid_and_simple()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "decimal",
            "name" => "test",
            "default" => 23.4
        ]);
        $field->validate();
        // ok if there's no exception
    }

    function test_self_validation_when_valid_and_complex()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "decimal",
            "name" => "My_test_test_23",
            "require" => true,
            "min" => 23,
            "max" => 99.999,
            "default" => 23.4
        ]);
        $field->validate();
        // ok if there's no exception
    }

    function test_self_validation_when_invalid()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "decimal",
            "name" => "x"
        ]);
        $this->setExpectedException(\App\Exceptions\MMValidationException::class);
        $field->validate();
    }

    function test_self_validation_expect_exception_when_name_has_space()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "decimal",
            "name" => "xwer werwer"
        ]);
        $this->setExpectedException(\App\Exceptions\MMValidationException::class);
        $field->validate();
    }

    function test_self_validation_expect_exception_when_name_starts_with_number()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "decimal",
            "name" => "1xwerwerwer"
        ]);
        $this->setExpectedException(\App\Exceptions\MMValidationException::class);
        $field->validate();
    }

    function test_self_validation_when_invalid2()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "decimal",
            "default" => "fish",
            "min" => true
        ]);
        $this->setExpectedException(\App\Exceptions\MMValidationException::class);
        $field->validate();
    }

    /*
     * currently validation of self doesn't check for unrecognised additional terms
     *
    function test_self_validation_when_invalid_with_extra_terms() {
        $field = App\Fields\Field::createFromData([
            "type" => "string",
            "name" => "x1234",
            "badextra" => 17
        ]);
        $field->validate();
    }
    */


    /**************************************
     * Validation for Options Field Type
     **************************************/

    function test_option_validation_code()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "option",
            "name" => "test",
            "options" => "a|Alpha\nb\nc"
        ]);
        $this->assertEquals("in:a,b,c|string", $field->valueValidationCode());
    }

    function test_option_validation_code_with_required()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "option",
            "name" => "test",
            "options" => "a|Alpha\nb\nc",
            "required" => true,
        ]);
        $this->assertEquals("in:a,b,c|required|string", $field->valueValidationCode());
    }

    function test_option_get_options()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "option",
            "name" => "test",
            "options" => "a|Alpha\nb\nc"
        ]);
        $this->assertArraySubset(array("a", "b", "c"), $field->options());
    }

    function test_option_get_options_different_order()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "option",
            "name" => "test",
            "options" => "c\nb|Badger\na"
        ]);
        $this->assertArraySubset(array("c", "b", "a"), $field->options());
    }


    function test_option_get_labels()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "option",
            "name" => "test",
            "options" => "a|Alpha\nb|Badger\nc"
        ]);
        $this->assertArraySubset(array("a" => "Alpha", "b" => "Badger", "c" => "c"), $field->optionsWithLabels());
    }

    function test_option_check_empty_line_is_ignored()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "option",
            "name" => "test",
            "options" => "a|Alpha\n\n|\nb|Badger\nc"
        ]);
        $this->assertArraySubset(array("a" => "Alpha", "b" => "Badger", "c" => "c"), $field->optionsWithLabels());
    }

    function test_option_check_zero_length_code_with_label()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "option",
            "name" => "test",
            "options" => "a|Alpha\n|Zero\n|\nb|Badger\nc"
        ]);
        $this->assertArraySubset(array("a" => "Alpha", "" => "Zero", "b" => "Badger", "c" => "c"), $field->optionsWithLabels());
    }

    function test_option_check_duplicate_codes_fail_validation()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "option",
            "name" => "test",
            "options" => "a|Alpha\nb|Badger\nc\nb"
        ]);
        $this->setExpectedException(\App\Exceptions\MMValidationException::class);
        $field->validate();
    }


    function test_option_check_duplicate_labels_fail_validation()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "option",
            "name" => "test",
            "options" => "a|Alpha\nb|Badger\nc\nb|Alpha"
        ]);
        $this->setExpectedException(\App\Exceptions\MMValidationException::class);
        $field->validate();
    }

    function test_option_check_lack_of_any_codes_fails_validation()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "option",
            "name" => "test",
            "options" => ""
        ]);
        $this->setExpectedException(\App\Exceptions\MMValidationException::class);
        $field->validate();
    }


    function test_option_check_comma_in_a_code_fails_validation()
    {
        $field = App\Fields\Field::createFromData([
            "type" => "option",
            "name" => "test",
            "options" => "a|Alpha\nb,c|Badger"
        ]);
        $this->setExpectedException(\App\Exceptions\MMValidationException::class);
        $field->validate();
    }
}

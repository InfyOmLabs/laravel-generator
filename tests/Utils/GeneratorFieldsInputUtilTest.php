<?php

namespace Tests;

use InfyOm\Generator\Utils\GeneratorFieldsInputUtil;

class GeneratorFieldsInputUtilTest extends \PHPUnit_Framework_TestCase
{
    public function testValidateFieldInput()
    {
        // invalid, only name, without database type
        $input = 'title';

        $res = GeneratorFieldsInputUtil::validateFieldInput($input);
        $this->assertFalse($res);

        // valid, with database type
        $input = 'title string';

        $res = GeneratorFieldsInputUtil::validateFieldInput($input);
        $this->assertTrue($res);
    }

    public function testProcessFieldInput()
    {
        // name, db_type and html_type
        $input = 'title string text';
        $validations = 'required';

        $res = GeneratorFieldsInputUtil::processFieldInput($input, $validations);
        $this->assertEquals($validations, $res->validations);
        $this->assertEquals('title', $res->name);

        $this->assertEquals('string', $res->dbInput);
        $this->assertEquals('$table->string(\'title\');', $res->migrationText);

        $this->assertEquals('text', $res->htmlType);
        $this->assertEquals('text', $res->htmlType);
        $this->assertEmpty($res->htmlValues);

        //
    }
}

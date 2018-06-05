<?php

namespace Tests;

use InfyOm\Generator\Utils\GeneratorFieldsInputUtil;

class GeneratorFieldsInputUtilTest extends \PHPUnit_Framework_TestCase
{
    public function testValidateFieldInput()
    {
        // invalid
        // only name, without database type
        $input = 'title';

        $res = GeneratorFieldsInputUtil::validateFieldInput($input);
        $this->assertFalse($res);

        // valid
        // with database type
        $input = 'title string';

        $res = GeneratorFieldsInputUtil::validateFieldInput($input);
        $this->assertTrue($res);
    }
}

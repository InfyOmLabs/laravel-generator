<?php

namespace Tests;

use InfyOm\Generator\Utils\GeneratorFieldsInputUtil;
use PHPUnit\Framework\TestCase;

class GeneratorFieldsInputUtilTest extends TestCase
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
        $this->assertEmpty($res->htmlValues);

        $this->assertTrue($res->isSearchable);
        $this->assertTrue($res->isFillable);
        $this->assertFalse($res->isPrimary);
        $this->assertTrue($res->inForm);
        $this->assertTrue($res->inIndex);
        $this->assertTrue($res->inView);

        // name string,20 textarea
        $input = 'name string,20 textarea';
        $res = GeneratorFieldsInputUtil::processFieldInput($input, '');
        $this->assertEquals('name', $res->name);

        $this->assertEquals('string,20', $res->dbInput);
        $this->assertEquals('$table->string(\'name\', 20);', $res->migrationText);

        $this->assertEquals('textarea', $res->htmlType);
        $this->assertEmpty($res->htmlValues);

        // post_id integer:unsigned:nullable
        $input = 'post_id integer:unsigned:nullable';
        $res = GeneratorFieldsInputUtil::processFieldInput($input, '');
        $this->assertEquals('post_id', $res->name);

        $this->assertEquals('integer:unsigned:nullable', $res->dbInput);
        $this->assertEquals('$table->integer(\'post_id\')->unsigned()->nullable();', $res->migrationText);

        $this->assertNull($res->htmlType);
        $this->assertEmpty($res->foreignKeyText);

        // post_id integer:unsigned:nullable:foreign,posts,id
        $input = 'post_id integer:unsigned:nullable:foreign,posts,id';
        $res = GeneratorFieldsInputUtil::processFieldInput($input, '');
        $this->assertEquals('post_id', $res->name);

        $this->assertEquals('integer:unsigned:nullable:foreign,posts,id', $res->dbInput);
        $this->assertEquals('$table->integer(\'post_id\')->unsigned()->nullable();', $res->migrationText);
        $this->assertEquals('$table->foreign(\'post_id\')->references(\'id\')->on(\'posts\');', $res->foreignKeyText);

        $this->assertNull($res->htmlType);

        // name, db_type and html_type if,s
        $input = 'title string text if,s';
        $validations = 'required';

        $res = GeneratorFieldsInputUtil::processFieldInput($input, $validations);
        $this->assertEquals($validations, $res->validations);
        $this->assertEquals('title', $res->name);

        $this->assertEquals('string', $res->dbInput);
        $this->assertEquals('$table->string(\'title\');', $res->migrationText);

        $this->assertEquals('text', $res->htmlType);
        $this->assertEmpty($res->htmlValues);

        $this->assertFalse($res->isSearchable);
        $this->assertTrue($res->isFillable);
        $this->assertFalse($res->isPrimary);
        $this->assertFalse($res->inForm);
        $this->assertTrue($res->inIndex);
        $this->assertTrue($res->inView);
    }

    public function testPrepareKeyValueArrayStr()
    {
        $arr = ['a' => 'A', 'b' => 'B'];

        $res = GeneratorFieldsInputUtil::prepareKeyValueArrayStr($arr);
        $expected = '[\'A\' => \'a\', \'B\' => \'b\']';

        $this->assertEquals($expected, $res);
    }

    public function testPrepareValuesArrayStr()
    {
        $arr = ['A', 'B', 'C'];

        $res = GeneratorFieldsInputUtil::prepareValuesArrayStr($arr);
        $expected = '[\'A\', \'B\', \'C\']';

        $this->assertEquals($expected, $res);
    }

    public function testKeyValueArrFromLabelValueStr()
    {
        $arr = ['A', 'B', 'C'];

        $res = GeneratorFieldsInputUtil::prepareKeyValueArrFromLabelValueStr($arr);
        $expected = ['A' => 'A', 'B' => 'B', 'C' => 'C'];

        $this->assertEquals($expected, $res);

        $arr = ['A:aa', 'B:bb', 'C:cc'];

        $res = GeneratorFieldsInputUtil::prepareKeyValueArrFromLabelValueStr($arr);
        $expected = ['A' => 'aa', 'B' => 'bb', 'C' => 'cc'];

        $this->assertEquals($expected, $res);
    }
}

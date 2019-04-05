<?php

namespace Tests\Generators;

use InfyOm\Generator\Generators\ModelGenerator;
use PHPUnit_Framework_TestCase;
use stdClass;

class ModelGeneratorTests extends PHPUnit_Framework_TestCase
{
    public function testGetPhpDocType()
    {
        // prepare properties to set into model
        $object = new stdClass();
        $object->config = new stdClass();
        $object->config->nsModel = 'App\Models';

        // mock model and set properties
        $modelGenerator = $this->mockClassExceptMethods(ModelGenerator::class, ['getPHPDocType']);
        $modelGenerator->commandData = $object;

        $inputs = ['datetime' => 'string|\Carbon\Carbon', 'string' => 'string'];
        foreach ($inputs as $dbType => $input) {
            $response = $modelGenerator->getPHPDocType($dbType);
            $this->assertEquals($input, $response);
        }

        $relationObj = new stdClass();
        $relationObj->inputs[0] = 'ModelName';
        $relationObj->inputs[1] = 'relation_id';

        $inputs = [
            '\App\Models\ModelName relation'                      => ['1t1', 'mt1'],
            '\Illuminate\Database\Eloquent\Collection modelNames' => ['1tm', 'mtm', 'hmt'],
        ];
        foreach ($inputs as $expected => $dbTypes) {
            foreach ($dbTypes as $dbType) {
                $response = $modelGenerator->getPHPDocType($dbType, $relationObj);
                $this->assertEquals($expected, $response);
            }
        }
    }

    public function testReturnGivenTypeItSelfWhenNoMatchingTypesFound()
    {
        $modelGenerator = $this->mockClassExceptMethods(ModelGenerator::class, ['getPHPDocType']);

        $response = $modelGenerator->getPHPDocType('integer');
        $this->assertEquals('integer', $response);
    }

    public function testGenerateRequireFields()
    {
        $fields = $this->prepareFields([
            ['name' => 'non_required_field', 'validations' => ''],
            ['name' => 'required_field', 'validations' => 'required'],
        ]);

        $modelGenerator = $this->mockClassExceptMethods(ModelGenerator::class, ['generateRequiredFields']);
        $modelGenerator->commandData = $fields;

        $response = $modelGenerator->generateRequiredFields();
        $this->assertContains('required_field', $response);
    }

    public function testReturnEmptyWhenAllFieldAreNonRequired()
    {
        $fields = $this->prepareFields([
            ['name' => 'non_required_field_1', 'validations' => ''],
            ['name' => 'non_required_field_2', 'validations' => ''],
        ]);

        $modelGenerator = $this->mockClassExceptMethods(ModelGenerator::class, ['generateRequiredFields']);
        $modelGenerator->commandData = $fields;

        $response = $modelGenerator->generateRequiredFields();
        $this->assertEmpty($response);
    }

    public function mockClassExceptMethods($className, $methods)
    {
        return $this->getMockBuilder($className)
            ->setMethodsExcept($methods)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function prepareFields($fields)
    {
        $objects = [];
        foreach ($fields as $field) {
            $object = new stdClass();
            foreach ($field as $key => $value) {
                $object->$key = $value;
            }
            $objects[] = $object;
        }

        $fields = new stdClass();
        $fields->fields = $objects;

        return $fields;
    }
}

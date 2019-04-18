<?php

namespace Tests\Generators;

use InfyOm\Generator\Generators\ModelGenerator;
use PHPUnit_Framework_TestCase;
use stdClass;
use Tests\Traits\CommonTrait;

class ModelGeneratorTests extends PHPUnit_Framework_TestCase
{
    use CommonTrait;

    public function testGetPhpDocType()
    {
        // prepare properties to set into model
        $object = new stdClass();
        $object->config = new stdClass();
        $object->config->nsModel = 'App\Models';

        // mock model and set properties
        /** @var ModelGenerator $modelGenerator */
        $modelGenerator = $this->mockClassExceptMethods(ModelGenerator::class, ['getPHPDocType']);
        $modelGenerator->commandData = $object;

        $inputs = ['datetime' => 'string|\Carbon\Carbon', 'string' => 'string'];
        foreach ($inputs as $dbType => $expected) {
            $response = $modelGenerator->getPHPDocType($dbType);
            $this->assertEquals($expected, $response);
        }

        $relationObj = new stdClass();
        $relationObj->inputs[0] = 'Business'; // model name
        $relationObj->inputs[1] = 'business_id'; // foreign key

        $inputs = [
            '\App\Models\Business business'                       => ['1t1', 'mt1'],
            '\Illuminate\Database\Eloquent\Collection businesses' => ['1tm', 'mtm', 'hmt'],
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
        /** @var ModelGenerator $modelGenerator */
        $modelGenerator = $this->mockClassExceptMethods(ModelGenerator::class, ['getPHPDocType']);

        $response = $modelGenerator->getPHPDocType('integer');
        $this->assertEquals('integer', $response);
    }

    public function testGenerateRequireFields()
    {
        $fields = $this->prepareFields([
            ['name' => 'business_id', 'validations' => ''], // optional field
            ['name' => 'location_id', 'validations' => 'required'], // required field should return
        ]);

        /** @var ModelGenerator $modelGenerator */
        $modelGenerator = $this->mockClassExceptMethods(ModelGenerator::class, ['generateRequiredFields']);
        $modelGenerator->commandData = $fields;

        $response = $modelGenerator->generateRequiredFields();
        $this->assertContains('location_id', $response);
    }

    public function testReturnEmptyWhenAllFieldAreNonRequired()
    {
        // both fields are optional
        $fields = $this->prepareFields([
            ['name' => 'business_id', 'validations' => ''],
            ['name' => 'location_id', 'validations' => ''],
        ]);

        /** @var ModelGenerator $modelGenerator */
        $modelGenerator = $this->mockClassExceptMethods(ModelGenerator::class, ['generateRequiredFields']);
        $modelGenerator->commandData = $fields;

        $response = $modelGenerator->generateRequiredFields();
        $this->assertEmpty($response);
    }
}

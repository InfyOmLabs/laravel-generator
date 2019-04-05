<?php namespace Tests\Generators;

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
        $modelGenerator = $this->getMockBuilder(ModelGenerator::class)
            ->setMethodsExcept(['getPHPDocType'])
            ->disableOriginalConstructor()
            ->getMock();
        $modelGenerator->commandData = $object;

        $inputs = ['datetime' => 'string|\Carbon\Carbon', 'string' => 'string'];
        foreach ($inputs as $dbType => $input) {
            $actualOutput = $modelGenerator->getPHPDocType($dbType);
            $this->assertEquals($input, $actualOutput);
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
                $actualOutput = $modelGenerator->getPHPDocType($dbType, $relationObj);
                $this->assertEquals($expected, $actualOutput);
            }
        }
    }

    public function testReturnGivenTypeItSelfWhenNoMatchingTypesFound()
    {
        $modelGenerator = $this->getMockBuilder(ModelGenerator::class)
            ->setMethodsExcept(['getPHPDocType'])
            ->disableOriginalConstructor()
            ->getMock();

        $response = $modelGenerator->getPHPDocType('integer');
        $this->assertEquals('integer', $response);
    }
}
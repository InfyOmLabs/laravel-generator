<?php namespace Tests\Generators;

use InfyOm\Generator\Generators\TestTraitGenerator;
use PHPUnit_Framework_TestCase;
use Tests\Traits\CommonTrait;
class TestTraitGeneratorTest extends PHPUnit_Framework_TestCase
{
    use CommonTrait;

    public function testGenerateFields()
    {
        $fields = $this->prepareFields([
            ['name' => 'filed_name_1', 'fieldType' => 'integer', 'isPrimary' => false],
            ['name' => 'filed_name_2', 'fieldType' => 'float', 'isPrimary' => false],
            ['name' => 'filed_name_3', 'fieldType' => 'string', 'isPrimary' => false],
            ['name' => 'filed_name_4', 'fieldType' => 'text', 'isPrimary' => false],
            ['name' => 'filed_name_5', 'fieldType' => 'datetime', 'isPrimary' => false],
            ['name' => 'filed_name_6', 'fieldType' => 'timestamp', 'isPrimary' => false],
            [
                'name'       => 'filed_name_7',
                'fieldType'  => 'enum',
                'isPrimary'  => false,
                'htmlValues' => ['value1', 'value2'],
            ],
        ]);

        $traitGenerator = $this->mockClassExceptMethods(TestTraitGenerator::class, ['generateFields']);
        $traitGenerator->commandData = $fields;

        $expectedOutput = [
            "'filed_name_1' => ".'$fake->randomDigitNotNull',
            "'filed_name_2' => ".'$fake->randomDigitNotNull',
            "'filed_name_3' => ".'$fake->word',
            "'filed_name_4' => ".'$fake->text',
            "'filed_name_5' => ".'$fake->date('."'Y-m-d H:i:s'".')',
            "'filed_name_6' => ".'$fake->date('."'Y-m-d H:i:s'".')',
            "'filed_name_7' => ".'$fake->randomElement(['."'value1', "."'value2'".'])',
        ];

        $response = $traitGenerator->generateFields();

        $this->assertCount(7, $response);
        for ($i = 0; $i < count($response); $i++) {
            $this->assertEquals($expectedOutput[$i], $response[$i]);
        }
    }

    public function testGenerateFieldAsTypeWordWhenNoMatchingTypesFound()
    {
        $fields = $this->prepareFields([
            ['name' => 'filed_name', 'fieldType' => 'db_type_not_found', 'isPrimary' => false],
        ]);

        $traitGenerator = $this->mockClassExceptMethods(TestTraitGenerator::class, ['generateFields']);
        $traitGenerator->commandData = $fields;

        $response = $traitGenerator->generateFields();

        $this->assertCount(1, $response);
        $this->assertEquals("'filed_name' => ".'$fake->word', $response[0]);
    }

    public function testNotGenerateFieldsForPrimaryField()
    {
        $fields = $this->prepareFields([
            ['name' => 'filed_name', 'fieldType' => 'integer', 'isPrimary' => true],
        ]);

        $traitGenerator = $this->mockClassExceptMethods(TestTraitGenerator::class, ['generateFields']);
        $traitGenerator->commandData = $fields;

        $response = $traitGenerator->generateFields();

        $this->assertEmpty($response);
    }
}
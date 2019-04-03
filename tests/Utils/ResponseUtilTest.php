<?php

namespace Tests\Utils;

use InfyOm\Generator\Utils\ResponseUtil;
use org\bovigo\vfs\vfsStream;
use Tests\TestCase;

class ResponseUtilTest extends TestCase
{
    public function testMakeResponse()
    {
        $message = 'Data Received';
        $data = ['field' => 'value'];

        $response = ResponseUtil::makeResponse($message, $data);

        $this->assertTrue($response['success']);
        $this->assertEquals($message, $response['message']);
        $this->assertEquals($data, $response['data']);
    }

    public function testMakeError()
    {
        $message = 'Error Occurred';

        $response = ResponseUtil::makeError($message);

        $this->assertFalse($response['success']);
        $this->assertEquals($message, $response['message']);
        $this->assertArrayNotHasKey('data', $response);
    }

    public function testMakeErrorWithGivenData()
    {
        $message = 'Error Occurred';
        $data = ['code' => '404', 'line' => 20];

        $response = ResponseUtil::makeError($message, $data);

        $this->assertFalse($response['success']);
        $this->assertEquals($message, $response['message']);
        $this->assertEquals($data, $response['data']);
    }

    public function testCreateMigrationFile()
    {
        $root = vfsStream::setup();

        $expectedFile = __DIR__.'/../Contents/migration.php';
        $fieldFile = __DIR__.'/../Contents/test_fields_sample.json';

        $this->artisan(
            'infyom:scaffold', ['model' => 'Test', '--fieldsFile' => $fieldFile]
        );

        $migrationsDir = $root->getChild('Migrations');
        $generatedMigrationFileName = $migrationsDir->getChildren()[0]->getName();

        $generatedFileContents = file_get_contents(vfsStream::url('root/Migrations/'.$generatedMigrationFileName));
        $expectedFileContents = file_get_contents($expectedFile);

        $this->assertEquals($expectedFileContents, $generatedFileContents);
    }
}

<?php

namespace InfyOm\Generator\Generators\API;

use InfyOm\Generator\Generators\BaseGenerator;

class APITestGenerator extends BaseGenerator
{
    private string $fileName;

    public function __construct()
    {
        parent::__construct();

        $this->path = $this->config->paths->apiTests;
        $this->fileName = $this->config->modelNames->name.'ApiTest.php';
    }

    public function generate()
    {
        $templateData = get_template('api.test.api_test', 'laravel-generator');

        $templateData = fill_template($this->config->dynamicVars, $templateData);

        g_filesystem()->createFile($this->path, $this->fileName, $templateData);

        $this->config->commandComment(PHP_EOL.'ApiTest created: ');
        $this->config->commandInfo($this->fileName);
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->config->commandComment('API Test file deleted: '.$this->fileName);
        }
    }
}

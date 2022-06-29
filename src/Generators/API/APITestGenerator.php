<?php

namespace InfyOm\Generator\Generators\API;

use InfyOm\Generator\Common\GeneratorConfig;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\FileUtil;

class APITestGenerator extends BaseGenerator
{
    private GeneratorConfig $config;

    private string $path;

    private string $fileName;

    public function __construct(GeneratorConfig $config)
    {
        $this->config = $config;
        $this->path = $this->config->paths->apiTests;
        $this->fileName = $this->config->modelNames->name.'ApiTest.php';
    }

    public function generate()
    {
        $templateData = get_template('api.test.api_test', 'laravel-generator');

        $templateData = fill_template($this->config->dynamicVars, $templateData);

        FileUtil::createFile($this->path, $this->fileName, $templateData);

        $this->config->commandComment("\nApiTest created: ");
        $this->config->commandInfo($this->fileName);
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->config->commandComment('API Test file deleted: '.$this->fileName);
        }
    }
}

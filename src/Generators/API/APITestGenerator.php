<?php

namespace InfyOm\Generator\Generators\API;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Utils\FileUtil;
use InfyOm\Generator\Utils\TemplateUtil;

class ApiTestGenerator
{
    /** @var  CommandData */
    private $commandData;

    /** @var string */
    private $path;

    function __construct($commandData)
    {
        $this->commandData = $commandData;
        $this->path = config('infyom.laravel_generator.path.api_test', base_path('tests/'));
    }

    function generate()
    {
        $templateData = TemplateUtil::getTemplate("api.test.api_test", "laravel-generator");

        $templateData = TemplateUtil::fillTemplate($this->commandData->dynamicVars, $templateData);

        $fileName = $this->commandData->modelName . "ApiTest.php";

        FileUtil::createFile($this->path, $fileName, $templateData);

        $this->commandData->commandObj->comment("\nApiTest created: ");
        $this->commandData->commandObj->info($fileName);
    }
}
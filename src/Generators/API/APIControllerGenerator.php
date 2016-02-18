<?php

namespace InfyOm\Generator\Generators\API;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Utils\FileUtil;
use InfyOm\Generator\Utils\TemplateUtil;

class APIControllerGenerator
{
    /** @var  CommandData */
    private $commandData;

    /** @var string */
    private $path;

    public function __construct($commandData)
    {
        $this->commandData = $commandData;
        $this->path = config('infyom.laravel_generator.path.api_controller', app_path('Http/Controllers/API/')
        );
    }

    public function generate()
    {
        $templateData = TemplateUtil::getTemplate('api.controller.api_controller', 'laravel-generator');

        $templateData = TemplateUtil::fillTemplate($this->commandData->dynamicVars, $templateData);
        $templateData = $this->fillDocs($templateData);

        $fileName = $this->commandData->modelName.'APIController.php';

        FileUtil::createFile($this->path, $fileName, $templateData);

        $this->commandData->commandComment("\nAPI Controller created: ");
        $this->commandData->commandInfo($fileName);
    }

    private function fillDocs($templateData)
    {
        $methods = ['controller', 'index', 'store', 'store', 'show', 'update', 'destroy'];

        if ($this->commandData->getAddOn('swagger')) {
            $templatePrefix = 'controller';
            $templateType = 'swagger-generator';
        } else {
            $templatePrefix = 'api.docs.controller';
            $templateType = 'laravel-generator';
        }

        foreach ($methods as $method) {
            $key = '$DOC_'.strtoupper($method).'$';
            $docTemplate = TemplateUtil::getTemplate($templatePrefix.'.'.$method, $templateType);
            $docTemplate = TemplateUtil::fillTemplate($this->commandData->dynamicVars, $docTemplate);
            $templateData = str_replace($key, $docTemplate, $templateData);
        }

        return $templateData;
    }
}

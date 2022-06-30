<?php

namespace InfyOm\Generator\Generators\API;

use InfyOm\Generator\Common\GeneratorConfig;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\FileUtil;

class APIControllerGenerator extends BaseGenerator
{
    private GeneratorConfig $config;

    private string $path;

    private string $fileName;

    public function __construct(GeneratorConfig $config)
    {
        $this->config = $config;
        $this->path = $this->config->paths->apiController;
        $this->fileName = $this->config->modelNames->name.'APIController.php';
    }

    /**
     * Generate API Controller Class.
     *
     * @return void
     */
    public function generate()
    {
        if ($this->config->options->repositoryPattern) {
            $templateName = 'api_controller';
        } else {
            $templateName = 'model_api_controller';
        }

        if ($this->config->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }

        if ($this->config->options->resources) {
            $templateName .= '_resource';
        }

        $templateData = get_template("api.controller.$templateName", 'laravel-generator');

        $templateData = fill_template($this->config->dynamicVars, $templateData);
        $templateData = $this->fillDocs($templateData);

        FileUtil::createFile($this->path, $this->fileName, $templateData);

        $this->config->commandComment(PHP_EOL."API Controller created: ");
        $this->config->commandInfo($this->fileName);
    }

    private function fillDocs($templateData)
    {
        $methods = ['controller', 'index', 'store', 'show', 'update', 'destroy'];

        if ($this->config->addons->swagger) {
            $templatePrefix = 'controller_docs';
            $templateType = 'swagger-generator';
        } else {
            $templatePrefix = 'api.docs.controller';
            $templateType = 'laravel-generator';
        }

        foreach ($methods as $method) {
            $key = '$DOC_'.strtoupper($method).'$';
            $docTemplate = get_template($templatePrefix.'.'.$method, $templateType);
            $docTemplate = fill_template($this->config->dynamicVars, $docTemplate);
            $templateData = str_replace($key, $docTemplate, $templateData);
        }

        return $templateData;
    }

    /**
     * Delete API Controller.
     *
     * @return void
     */
    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->config->commandComment('API Controller file deleted: '.$this->fileName);
        }
    }
}

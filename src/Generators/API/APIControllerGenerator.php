<?php

namespace InfyOm\Generator\Generators\API;

use InfyOm\Generator\Generators\BaseGenerator;

class APIControllerGenerator extends BaseGenerator
{
    private string $fileName;

    public function __construct()
    {
        parent::__construct();

        $this->path = $this->config->paths->apiController;
        $this->fileName = $this->config->modelNames->name.'APIController.php';
    }

    public function generate()
    {
        if ($this->config->options->repositoryPattern) {
            $templateName = 'repository.controller';
        } else {
            $templateName = 'model.controller';
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

        g_filesystem()->createFile($this->path, $this->fileName, $templateData);

        $this->config->commandComment(PHP_EOL.'API Controller created: ');
        $this->config->commandInfo($this->fileName);
    }

    private function fillDocs(string $templateData): string
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

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->config->commandComment('API Controller file deleted: '.$this->fileName);
        }
    }
}

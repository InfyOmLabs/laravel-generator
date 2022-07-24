<?php

namespace InfyOm\Generator\Generators\API;

use Illuminate\Support\Str;
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

    public function variables(): array
    {
        return array_merge([], $this->docsVariables());
    }

    public function getViewName(): string
    {
        if ($this->config->options->repositoryPattern) {
            $templateName = 'repository.controller';
        } else {
            $templateName = 'model.controller';
        }

        if ($this->config->options->resources) {
            $templateName .= '_resource';
        }

        return $templateName;
    }

    public function generate()
    {
        $viewName = $this->getViewName();

        $templateData = view('laravel-generator::api.controller.'.$viewName, $this->variables())->render();

        g_filesystem()->createFile($this->path.$this->fileName, $templateData);

        $this->config->commandComment(infy_nl().'API Controller created: ');
        $this->config->commandInfo($this->fileName);
    }

    protected function docsVariables(): array
    {
        $methods = ['controller', 'index', 'store', 'show', 'update', 'destroy'];

        if ($this->config->options->swagger) {
            $templatePrefix = 'controller';
            $templateType = 'swagger-generator';
        } else {
            $templatePrefix = 'api.docs.controller';
            $templateType = 'laravel-generator';
        }

        $variables = [];
        foreach ($methods as $method) {
            $variable = 'doc'.Str::title($method);
            $variables[$variable] = view($templateType.'::'.$templatePrefix.'.'.$method)->render();
        }

        return $variables;
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->config->commandComment('API Controller file deleted: '.$this->fileName);
        }
    }
}

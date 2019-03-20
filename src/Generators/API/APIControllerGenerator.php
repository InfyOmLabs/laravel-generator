<?php

namespace InfyOm\Generator\Generators\API;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\FileUtil;

class APIControllerGenerator extends BaseGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    /** @var string */
    private $fileName;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathApiController;
        $this->fileName = $this->commandData->modelName.'APIController.php';
    }

    public function generate()
    {
        $validatedData = '';
        $templateData = get_template('api.controller.api_controller', 'laravel-generator');
        $validationData = get_template('api.controller.validations', 'laravel-generator');

        $foreignFields = array_filter($this->commandData->fields, function ($val) {
            return !empty($val->foreignTable);
        });

        foreach ($foreignFields as $foreignField) {
            $modelName = model_name_from_table_name($foreignField->foreignTable);
            $keyName = $foreignField->name;

            $fullModelPath = '\\'.$this->commandData->config->nsModel.'\\'.$modelName;
            $this->commandData->addDynamicVariable('$MODEL_EXIST$', snake_case($modelName));
            $this->commandData->addDynamicVariable('$FOREIGN_KEY', $keyName);
            $this->commandData->addDynamicVariable('$FULL_MODEL_PATH', $fullModelPath);
            $filledData = fill_template($this->commandData->dynamicVars, $validationData);
            $validatedData .= $filledData;
        }

        $this->commandData->addDynamicVariable('$MODEL_VALIDATIONS$', $validatedData);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);
        $templateData = $this->fillDocs($templateData);

        FileUtil::createFile($this->path, $this->fileName, $templateData);

        $this->commandData->commandComment("\nAPI Controller created: ");
        $this->commandData->commandInfo($this->fileName);
    }

    private function fillDocs($templateData)
    {
        $methods = ['controller', 'index', 'store', 'show', 'update', 'destroy'];

        if ($this->commandData->getAddOn('swagger')) {
            $templatePrefix = 'controller_docs';
            $templateType = 'swagger-generator';
        } else {
            $templatePrefix = 'api.docs.controller';
            $templateType = 'laravel-generator';
        }

        foreach ($methods as $method) {
            $key = '$DOC_'.strtoupper($method).'$';
            $docTemplate = get_template($templatePrefix.'.'.$method, $templateType);
            $docTemplate = fill_template($this->commandData->dynamicVars, $docTemplate);
            $templateData = str_replace($key, $docTemplate, $templateData);
        }

        return $templateData;
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->commandData->commandComment('API Controller file deleted: '.$this->fileName);
        }
    }
}

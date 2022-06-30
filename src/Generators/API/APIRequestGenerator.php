<?php

namespace InfyOm\Generator\Generators\API;

use InfyOm\Generator\Common\GeneratorConfig;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Generators\ModelGenerator;
use InfyOm\Generator\Utils\FileUtil;

class APIRequestGenerator extends BaseGenerator
{
    private GeneratorConfig $config;

    private string $path;

    private string $createFileName;

    private string $updateFileName;

    public function __construct(GeneratorConfig $config)
    {
        $this->config = $config;
        $this->path = $this->config->paths->apiRequest;
        $this->createFileName = 'Create'.$this->config->modelNames->name.'APIRequest.php';
        $this->updateFileName = 'Update'.$this->config->modelNames->name.'APIRequest.php';
    }

    /**
     * Generate API Request Class.
     *
     * @return void
     */
    public function generate()
    {
        $this->generateCreateRequest();
        $this->generateUpdateRequest();
    }

    /**
     * Generate Create Request.
     *
     * @return void
     */
    private function generateCreateRequest()
    {
        $templateData = get_template('api.request.create_request', 'laravel-generator');

        $templateData = fill_template($this->config->dynamicVars, $templateData);

        FileUtil::createFile($this->path, $this->createFileName, $templateData);

        $this->config->commandComment(PHP_EOL."Create Request created: ");
        $this->config->commandInfo($this->createFileName);
    }

    /**
     * Generate Update Request.
     *
     * @return void
     */
    private function generateUpdateRequest()
    {
        $modelGenerator = new ModelGenerator($this->config);
        $rules = $modelGenerator->generateUniqueRules();
        $this->config->addDynamicVariable('$UNIQUE_RULES$', $rules);

        $templateData = get_template('api.request.update_request', 'laravel-generator');

        $templateData = fill_template($this->config->dynamicVars, $templateData);

        FileUtil::createFile($this->path, $this->updateFileName, $templateData);

        $this->config->commandComment(PHP_EOL."Update Request created: ");
        $this->config->commandInfo($this->updateFileName);
    }

    /**
     * Delete the generated Request Classes.
     *
     * @return void
     */
    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->createFileName)) {
            $this->config->commandComment('Create API Request file deleted: '.$this->createFileName);
        }

        if ($this->rollbackFile($this->path, $this->updateFileName)) {
            $this->config->commandComment('Update API Request file deleted: '.$this->updateFileName);
        }
    }
}

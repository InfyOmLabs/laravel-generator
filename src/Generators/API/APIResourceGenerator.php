<?php

namespace InfyOm\Generator\Generators\API;

use InfyOm\Generator\Common\GeneratorConfig;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\FileUtil;

class APIResourceGenerator extends BaseGenerator
{
    private GeneratorConfig $config;

    private string $path;

    private string $fileName;

    public function __construct(GeneratorConfig $config)
    {
        $this->config = $config;
        $this->path = $this->config->paths->apiResource;
        $this->fileName = $this->config->modelNames->name.'Resource.php';
    }

    /**
     * Generate API Resources.
     *
     * @return void
     */
    public function generate()
    {
        $templateData = get_template('api.resource.api_resource', 'laravel-generator');

        $templateData = fill_template($this->config->dynamicVars, $templateData);

        $templateData = str_replace(
            '$RESOURCE_FIELDS$',
            implode(','.infy_nl_tab(1, 3), $this->generateResourceFields()),
            $templateData
        );

        FileUtil::createFile($this->path, $this->fileName, $templateData);

        $this->config->commandComment("\nAPI Resource created: ");
        $this->config->commandInfo($this->fileName);
    }

    private function generateResourceFields()
    {
        $resourceFields = [];
        foreach ($this->config->fields as $field) {
            $resourceFields[] = "'".$field->name."' => \$this->".$field->name;
        }

        return $resourceFields;
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->config->commandComment('API Resource file deleted: '.$this->fileName);
        }
    }
}

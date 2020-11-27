<?php

namespace InfyOm\Generator\Generators\API;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\FileUtil;

class APIResourceGenerator extends BaseGenerator
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
        $this->path = $commandData->config->pathApiResource;
        $this->fileName = $this->commandData->modelName.'Resource.php';
    }

    public function generate()
    {
        $templateData = get_template('api.resource.api_resource', 'laravel-generator');

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $templateData = str_replace(
            '$RESOURCE_FIELDS$',
            implode(','.infy_nl_tab(1, 3), $this->generateResourceFields()),
            $templateData
        );

        FileUtil::createFile($this->path, $this->fileName, $templateData);

        $this->commandData->commandComment("\nAPI Resource created: ");
        $this->commandData->commandInfo($this->fileName);
    }

    private function generateResourceFields()
    {
        $resourceFields = [];
        foreach ($this->commandData->fields as $field) {
            $resourceFields[] = "'".$field->name."' => \$this->".$field->name;
        }

        return $resourceFields;
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->commandData->commandComment('API Resource file deleted: '.$this->fileName);
        }
    }
}

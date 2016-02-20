<?php

namespace InfyOm\Generator\Generators;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Utils\FileUtil;
use InfyOm\Generator\Utils\TemplateUtil;

class RepositoryGenerator
{
    /** @var  CommandData */
    private $commandData;

    /** @var string */
    private $path;

    public function __construct($commandData)
    {
        $this->commandData = $commandData;
        $this->path = config('infyom.laravel_generator.path.repository', app_path('Repositories/'));
    }

    public function generate()
    {
        $templateData = TemplateUtil::getTemplate('repository', 'laravel-generator');

        $templateData = TemplateUtil::fillTemplate($this->commandData->dynamicVars, $templateData);

        $searchables = [];

        foreach ($this->commandData->inputFields as $field) {
            if ($field['searchable']) {
                $searchables[] = '"'.$field['fieldName'].'"';
            }
        }

        $templateData = str_replace('$FIELDS$', implode(",\n\t\t", $searchables), $templateData);

        $fileName = $this->commandData->modelName.'Repository.php';

        FileUtil::createFile($this->path, $fileName, $templateData);

        $this->commandData->commandComment("\nRepository created: ");
        $this->commandData->commandInfo($fileName);
    }
}

<?php

namespace InfyOm\Generator\Generators;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Utils\FileUtil;
use InfyOm\Generator\Utils\TemplateUtil;

class RepositoryGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathRepository;
    }

    public function generate()
    {
        $templateData = TemplateUtil::getTemplate('repository', 'laravel-generator');

        $templateData = TemplateUtil::fillTemplate($this->commandData->dynamicVars, $templateData);

        $searchables = [];

        foreach ($this->commandData->inputFields as $field) {
            if ($field['searchable']) {
                $searchables[] = "'".$field['fieldName']."'";
            }
        }

        $templateData = str_replace('$FIELDS$', implode(','.PHP_EOL.str_repeat(' ', 8), $searchables), $templateData);

        $fileName = $this->commandData->modelName.'Repository.php';

        FileUtil::createFile($this->path, $fileName, $templateData);

        $this->commandData->commandComment("\nRepository created: ");
        $this->commandData->commandInfo($fileName);
    }
}

<?php

namespace InfyOm\Generator\Generators\Scaffold;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\FileUtil;

class LanguageGenerator extends BaseGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    /** @var string */
    private $locale;

    /** @var string */
    private $languageFileName;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathLanguage;
        $this->locale = $commandData->config->locale;
        $this->languageFileName = $this->commandData->dynamicVars['$MODEL_NAME_PLURAL_CAMEL$'].'.php';
    }

    public function generate()
    {
        $templateData = get_template('lang.'.$this->locale.'.strings', 'laravel-generator');

        $templateData = $this->fill_template($templateData);

        FileUtil::createFile($this->path, $this->languageFileName, $templateData);

        $this->commandData->commandComment("\nLanguage created: ");
        $this->commandData->commandInfo($this->languageFileName);
    }

    private function fill_template($templateData)
    {
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $fields_name_label = '';

        foreach ($this->commandData->fields as $field) {
            $fields_name_label .= "'field.".$field->name."' => '".(isset($field->label) ? $field->label : $field->name)."',\n\t";
        }

        $templateData = str_replace('$FIELDS_NAME_LABEL$', $fields_name_label, $templateData);

        return $templateData;
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->createFileName)) {
            $this->commandData->commandComment('Create language files deleted: '.$this->languageFileName);
        }
    }
}

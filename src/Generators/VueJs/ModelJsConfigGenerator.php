<?php

namespace InfyOm\Generator\Generators\VueJs;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\FileUtil;

class ModelJsConfigGenerator extends BaseGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    /** @var string */
    private $fileName;

    /** @var string */
    private $templateType;

    /** @var array */
    private $htmlFields;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->modelJsPath;
        $this->fileName = $this->commandData->config->mCamel.'-config.js';
    }

    public function generate()
    {
        if (!file_exists($this->path)) {
            mkdir($this->path, 0755, true);
        }
        $this->commandData->commandComment("\nGenerating VueJsConfigModel...");
        $this->generateModelJs();
        $this->commandData->commandComment('ModelJsConfig created.');
    }

    private function generateModelJs()
    {
        $templateData = get_template('vuejs.js.model-config', 'laravel-generator');
        $fieldsRow = '';
        $i = 0;
        $lenghtFields = count($this->commandData->fields);
        foreach ($this->commandData->fields as $field) {
            if ($i == $lenghtFields - 1) {
                $fieldsRow .= "\t".$field->name.': ""';
            } else {
                $fieldsRow .= "\t".$field->name.": \"\",\n";
            }
            $i++;
        }
        $templateData = str_replace('$FIELDS_ROW$', $fieldsRow, $templateData);

        $fieldsColTemplateData = get_template('vuejs.js.fields_col', 'laravel-generator');
        $fieldsColTemplate = '';
        $i = 0;
        foreach ($this->commandData->fields as $field) {
            $fieldCol = $fieldsColTemplateData;
            $fieldCol = str_replace('$FIELD_NAME$', $field->name, $fieldCol);
            $fieldVisible = 'true';
            if (!$field->inIndex) {
                $fieldVisible = 'false';
            }
            $fieldCol = str_replace('$FIELD_VISIBLE$', $fieldVisible, $fieldCol);
            if ($i == $lenghtFields - 1) {
                $fieldsColTemplate .= $fieldCol;
            } else {
                $fieldsColTemplate .= $fieldCol."\n";
            }
            $i++;
        }
        $templateData = str_replace('$FIELDS_COL$', $fieldsColTemplate, $templateData);

        FileUtil::createFile($this->path, $this->fileName, $templateData);
        $this->commandData->commandInfo($this->path.$this->fileName.' created');
    }

    public function rollback()
    {
        $files = [
            $this->fileName,
        ];
        foreach ($files as $file) {
            if ($this->rollbackFile($this->path, $file)) {
                $this->commandData->commandComment($file.' file deleted');
            }
        }
    }
}

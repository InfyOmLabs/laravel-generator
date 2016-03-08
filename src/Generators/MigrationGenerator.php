<?php

namespace InfyOm\Generator\Generators;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Utils\FileUtil;
use InfyOm\Generator\Utils\SchemaUtil;
use InfyOm\Generator\Utils\TemplateUtil;

class MigrationGenerator
{
    /** @var  CommandData */
    private $commandData;

    /** @var string */
    private $path;

    public function __construct($commandData)
    {
        $this->commandData = $commandData;
        $this->path = config('infyom.laravel_generator.path.migration', base_path('database/migrations/'));
    }

    public function generate()
    {
        $templateData = TemplateUtil::getTemplate('migration', 'laravel-generator');

        $templateData = TemplateUtil::fillTemplate($this->commandData->dynamicVars, $templateData);

        $templateData = str_replace('$FIELDS$', $this->generateFields(), $templateData);

        $tableName = $this->commandData->dynamicVars['$TABLE_NAME$'];

        $fileName = date('Y_m_d_His').'_'.'create_'.$tableName.'_table.php';

        FileUtil::createFile($this->path, $fileName, $templateData);

        $this->commandData->commandComment("\nMigration created: ");
        $this->commandData->commandInfo($fileName);
    }

    private function generateFields()
    {
        $fields = [];

        foreach ($this->commandData->inputFields as $field) {
            if ($field['fieldName'] == 'created_at' or $field['fieldName'] == 'updated_at') {
                continue;
            }
            $fields[] = SchemaUtil::createField($field);
        }

        $fields[] = '$table->timestamps();';

        if ($this->commandData->getOption('softDelete')) {
            $fields[] = '$table->softDeletes();';
        }

        return implode(PHP_EOL . str_repeat(' ', 12), $fields);
    }
}

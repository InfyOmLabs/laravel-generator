<?php

namespace InfyOm\Generator\Generators;

use File;
use Illuminate\Support\Str;
use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Utils\FileUtil;
use InfyOm\Generator\Utils\SchemaUtil;
use InfyOm\Generator\Utils\TemplateUtil;
use SplFileInfo;

class MigrationGenerator extends BaseGenerator
{
    /** @var CommandData */
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

        return implode(infy_nl_tab(1, 3), $fields);
    }

    public function rollback()
    {
        $fileName = 'create_'.$this->commandData->config->tableName.'_table.php';

        /** @var SplFileInfo $allFiles */
        $allFiles = File::allFiles($this->path);

        $files = [];

        foreach ($allFiles as $file) {
            $files[] = $file->getFilename();
        }

        $files = array_reverse($files);

        foreach ($files as $file) {
            if (Str::contains($file, $fileName)) {
                if ($this->rollbackFile($this->path, $file)) {
                    $this->commandData->commandComment('Migration file deleted: '.$file);
                }
                break;
            }
        }
    }
}

<?php

namespace InfyOm\Generator\Generators;

use File;
use Illuminate\Support\Str;
use InfyOm\Generator\Common\GeneratorConfig;
use InfyOm\Generator\Utils\FileUtil;
use SplFileInfo;

class MigrationGenerator extends BaseGenerator
{
    private GeneratorConfig $config;

    private string $path;

    public function __construct(GeneratorConfig $config)
    {
        $this->config = $config;
        $this->path = config('laravel_generator.path.migration', database_path('migrations/'));
    }

    public function generate()
    {
        $templateData = get_template('migration', 'laravel-generator');

        $templateData = fill_template($this->config->dynamicVars, $templateData);

        $templateData = str_replace('$FIELDS$', $this->generateFields(), $templateData);

        $tableName = $this->config->dynamicVars['$TABLE_NAME$'];

        $fileName = date('Y_m_d_His').'_'.'create_'.strtolower($tableName).'_table.php';

        FileUtil::createFile($this->path, $fileName, $templateData);

        $this->config->commandComment("\nMigration created: ");
        $this->config->commandInfo($fileName);
    }

    private function generateFields()
    {
        $fields = [];
        $foreignKeys = [];
        $createdAtField = null;
        $updatedAtField = null;

        if (isset($this->config->fields) && !empty($this->config->fields)) {
            foreach ($this->config->fields as $field) {
                if ($field->name == 'created_at') {
                    $createdAtField = $field;
                    continue;
                } else {
                    if ($field->name == 'updated_at') {
                        $updatedAtField = $field;
                        continue;
                    }
                }

                $fields[] = $field->migrationText;
                if (!empty($field->foreignKeyText)) {
                    $foreignKeys[] = $field->foreignKeyText;
                }
            }
        }

        if ($createdAtField and $updatedAtField) {
            $fields[] = '$table->timestamps();';
        } else {
            if ($createdAtField) {
                $fields[] = $createdAtField->migrationText;
            }
            if ($updatedAtField) {
                $fields[] = $updatedAtField->migrationText;
            }
        }

        if ($this->config->options->softDelete) {
            $fields[] = '$table->softDeletes();';
        }

        return implode(infy_nl_tab(1, 3), array_merge($fields, $foreignKeys));
    }

    public function rollback()
    {
        $fileName = 'create_'.$this->config->tableName.'_table.php';

        /** @var SplFileInfo $allFiles */
        $allFiles = File::allFiles($this->path);

        $files = [];

        if (!empty($allFiles)) {
            foreach ($allFiles as $file) {
                $files[] = $file->getFilename();
            }

            $files = array_reverse($files);

            foreach ($files as $file) {
                if (Str::contains($file, $fileName)) {
                    if ($this->rollbackFile($this->path, $file)) {
                        $this->config->commandComment('Migration file deleted: '.$file);
                    }
                    break;
                }
            }
        }
    }
}

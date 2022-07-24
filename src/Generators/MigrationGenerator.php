<?php

namespace InfyOm\Generator\Generators;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use SplFileInfo;

class MigrationGenerator extends BaseGenerator
{
    public function __construct()
    {
        parent::__construct();

        $this->path = config('laravel_generator.path.migration', database_path('migrations/'));
    }

    public function generate()
    {
        $templateData = view('laravel-generator::migration', $this->variables())->render();

        $fileName = date('Y_m_d_His').'_'.'create_'.strtolower($this->config->tableName).'_table.php';

        g_filesystem()->createFile($this->path.$fileName, $templateData);

        $this->config->commandComment(infy_nl().'Migration created: ');
        $this->config->commandInfo($fileName);
    }

    public function variables(): array
    {
        return [
            'fields' => $this->generateFields(),
        ];
    }

    protected function generateFields(): string
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

        if ($createdAtField->name === 'created_at' and $updatedAtField->name === 'updated_at') {
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
            $softDeleteFieldName = config('laravel_generator.timestamps.deleted_at', 'deleted_at');
            if ($softDeleteFieldName === 'deleted_at') {
                $fields[] = '$table->softDeletes();';
            } else {
                $fields[] = '$table->softDeletes(\''.$softDeleteFieldName.'\');';
            }
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

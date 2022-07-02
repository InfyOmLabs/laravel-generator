<?php

namespace InfyOm\Generator\Generators\Scaffold;

use Exception;
use InfyOm\Generator\Generators\BaseGenerator;

class ControllerGenerator extends BaseGenerator
{
    private string $templateType;

    private string $fileName;

    public function __construct()
    {
        parent::__construct();

        $this->path = $this->config->paths->controller;
        $this->templateType = config('laravel_generator.templates', 'adminlte-templates');
        $this->fileName = $this->config->modelNames->name.'Controller.php';
    }

    public function generate()
    {
        switch ($this->config->tableType) {
            case 'blade':
                if ($this->config->options->repositoryPattern) {
                    $templateName = 'repository.controller';
                } else {
                    $templateName = 'model.controller';
                }
                if ($this->config->isLocalizedTemplates()) {
                    $templateName .= '_locale';
                }

                $templateData = get_template("scaffold.controller.$templateName", 'laravel-generator');

                $templateData = str_replace('$RENDER_TYPE$', 'paginate(10)', $templateData);
                break;

            case 'datatables':
                if ($this->config->options->repositoryPattern) {
                    $templateName = 'repository.datatable.controller';
                } else {
                    $templateName = 'model.datatable.controller';
                }

                if ($this->config->isLocalizedTemplates()) {
                    $templateName .= '_locale';
                }

                $templateData = get_template("scaffold.controller.$templateName", 'laravel-generator');

                $this->generateDataTable();
                break;

            case 'livewire':
                if ($this->config->options->repositoryPattern) {
                    $templateName = 'repository.livewire.controller';
                } else {
                    $templateName = 'model.livewire.controller';
                }

                if ($this->config->isLocalizedTemplates()) {
                    $templateName .= '_locale';
                }

                $templateData = get_template("scaffold.controller.$templateName", 'laravel-generator');

                $this->generateLivewireTable();
                break;

            default:
                throw new Exception('Invalid Table Type');
        }

        $templateData = fill_template($this->config->dynamicVars, $templateData);

        g_filesystem()->createFile($this->path, $this->fileName, $templateData);

        $this->config->commandComment(PHP_EOL.'Controller created: ');
        $this->config->commandInfo($this->fileName);
    }

    private function generateDataTable()
    {
        $templateName = 'table.datatable';
        if ($this->config->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }

        $templateData = get_template('scaffold.'.$templateName, 'laravel-generator');

        $templateData = fill_template($this->config->dynamicVars, $templateData);

        $templateData = str_replace(
            '$DATATABLE_COLUMNS$',
            implode(','.infy_nl_tab(1, 3), $this->generateDataTableColumns()),
            $templateData
        );

        $path = $this->config->paths->dataTables;

        $fileName = $this->config->modelNames->name.'DataTable.php';

        g_filesystem()->createFile($path, $fileName, $templateData);

        $this->config->commandComment(PHP_EOL.'DataTable created: ');
        $this->config->commandInfo($fileName);
    }

    private function generateLivewireTable()
    {
        $templateName = 'table.livewire';
        if ($this->config->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }

        $templateData = get_template('scaffold.'.$templateName, 'laravel-generator');

        $templateData = fill_template($this->config->dynamicVars, $templateData);

        $templateData = str_replace(
            '$LIVEWIRE_COLUMNS$',
            implode(','.infy_nl_tab(1, 3), $this->generateLivewireTableColumns()),
            $templateData
        );

        $path = $this->config->paths->livewireTables;

        $fileName = $this->config->modelNames->plural.'Table.php';

        g_filesystem()->createFile($path, $fileName, $templateData);

        $this->config->commandComment(PHP_EOL.'LivewireTable created: ');
        $this->config->commandInfo($fileName);
    }

    private function generateDataTableColumns(): array
    {
        $templateName = 'table.datatable.column';
        if ($this->config->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }
        $headerFieldTemplate = get_template('scaffold.views.'.$templateName, $this->templateType);

        $dataTableColumns = [];
        foreach ($this->config->fields as $field) {
            if (!$field->inIndex) {
                continue;
            }

            if ($this->config->isLocalizedTemplates() && !$field->isSearchable) {
                $headerFieldTemplate = str_replace('$SEARCHABLE$', ",'searchable' => false", $headerFieldTemplate);
            }

            $fieldTemplate = fill_template_with_field_data(
                $this->config->dynamicVars,
                ['$FIELD_NAME_TITLE$' => 'fieldTitle', '$FIELD_NAME$' => 'name'],
                $headerFieldTemplate,
                $field
            );

            if ($field->isSearchable) {
                $dataTableColumns[] = $fieldTemplate;
            } else {
                if ($this->config->isLocalizedTemplates()) {
                    $dataTableColumns[] = $fieldTemplate;
                } else {
                    $dataTableColumns[] = "'".$field->name."' => ['searchable' => false]";
                }
            }
        }

        return $dataTableColumns;
    }

    private function generateLivewireTableColumns(): array
    {
        $livewireTableColumns = [];

        foreach ($this->config->fields as $field) {
            if (!$field->inIndex) {
                continue;
            }

            $fieldTemplate = 'Column::make("$FIELD_NAME_TITLE$", "$FIELD_NAME$")'.PHP_EOL.infy_tabs(4).'->sortable()';

            $fieldTemplate = fill_template_with_field_data(
                $this->config->dynamicVars,
                ['$FIELD_NAME_TITLE$' => 'fieldTitle', '$FIELD_NAME$' => 'name'],
                $fieldTemplate,
                $field
            );

            if ($field->isSearchable) {
                $fieldTemplate .= PHP_EOL.infy_tabs(4).'->searchable()';
            }

            $livewireTableColumns[] = $fieldTemplate;
        }

        return $livewireTableColumns;
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->config->commandComment('Controller file deleted: '.$this->fileName);
        }

        if ($this->config->tableType === 'datatables') {
            if ($this->rollbackFile(
                $this->config->paths->dataTables,
                $this->config->modelNames->name.'DataTable.php'
            )) {
                $this->config->commandComment('DataTable file deleted: '.$this->fileName);
            }
        }
    }
}

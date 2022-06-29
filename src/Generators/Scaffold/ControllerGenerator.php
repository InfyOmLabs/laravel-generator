<?php

namespace InfyOm\Generator\Generators\Scaffold;

use InfyOm\Generator\Common\GeneratorConfig;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\FileUtil;

class ControllerGenerator extends BaseGenerator
{
    private GeneratorConfig $config;

    private string $path;

    private string $templateType;

    private string $fileName;

    public function __construct(GeneratorConfig $config)
    {
        $this->config = $config;
        $this->path = $this->config->paths->controller;
        $this->templateType = config('laravel_generator.templates', 'adminlte-templates');
        $this->fileName = $this->config->modelNames->name . 'Controller.php';
    }

    public function generate()
    {
        switch ($this->config->tableType) {
            case 'blade':
                $templateName = 'model_datatable_controller';
                break;

            case 'datatables':
                $templateName = 'datatable_controller';
                break;

            case 'livewire':
                $templateName = 'livewire_table_controller';
                break;

        }

        if ($this->config->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }

        $templateData = get_template("scaffold.controller.$templateName", 'laravel-generator');

        $this->generateDataTable();

        if ($this->config->tableType == 'jqueryDT') {
            $templateName = 'jquery_datatable_controller';
            $templateData = get_template("scaffold.controller.$templateName", 'laravel-generator');

            $this->generateDataTable();
        } else {
            if ($this->config->options->repositoryPattern) {
                $templateName = 'controller';
            } else {
                $templateName = 'model_controller';
            }
            if ($this->config->isLocalizedTemplates()) {
                $templateName .= '_locale';
            }

            $templateData = get_template("scaffold.controller.$templateName", 'laravel-generator');

            $templateData = str_replace('$RENDER_TYPE$', 'paginate(' . $paginate . ')', $templateData);

            $templateData = fill_template($this->config->dynamicVars, $templateData);

            FileUtil::createFile($this->path, $this->fileName, $templateData);

            $this->config->commandComment("\nController created: ");
            $this->config->commandInfo($this->fileName);
        }
    }

    private function generateDataTable()
    {
        $templateName = ($this->config->tableType == 'jqueryDT') ? 'jquery_datatable' : 'datatable';
        if ($this->config->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }

        $templateData = get_template('scaffold.' . $templateName, 'laravel-generator');

        $templateData = fill_template($this->config->dynamicVars, $templateData);

        $templateData = str_replace(
            '$DATATABLE_COLUMNS$',
            implode(',' . infy_nl_tab(1, 3), $this->generateDataTableColumns()),
            $templateData
        );

        $path = $this->config->paths->dataTables;

        $fileName = $this->config->modelNames->name . 'DataTable.php';

        FileUtil::createFile($path, $fileName, $templateData);

        $this->config->commandComment("\nDataTable created: ");
        $this->config->commandInfo($fileName);
    }

    private function generateDataTableColumns()
    {
        $templateName = 'datatable_column';
        if ($this->config->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }
        $headerFieldTemplate = get_template('scaffold.views.' . $templateName, $this->templateType);

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
                ['$FIELD_NAME_TITLE$' => 'fieldTitle','$FIELD_NAME$' => 'name'],
                $headerFieldTemplate,
                $field
            );

            if ($field->isSearchable) {
                $dataTableColumns[] = $fieldTemplate;
            } else {
                if ($this->config->isLocalizedTemplates()) {
                    $dataTableColumns[] = $fieldTemplate;
                } else {
                    $dataTableColumns[] = "'" . $field->name . "' => ['searchable' => false]";
                }
            }
        }

        return $dataTableColumns;
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->config->commandComment('Controller file deleted: ' . $this->fileName);
        }

        if ($this->config->tableType === 'datatables') {
            if ($this->rollbackFile(
                $this->config->paths->dataTables,
                $this->config->modelNames->name . 'DataTable.php'
            )) {
                $this->config->commandComment('DataTable file deleted: ' . $this->fileName);
            }
        }
    }
}

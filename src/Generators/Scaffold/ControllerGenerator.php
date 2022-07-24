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
        $variables = [];

        switch ($this->config->tableType) {
            case 'blade':
                if ($this->config->options->repositoryPattern) {
                    $indexMethodView = 'index_method_repository';
                } else {
                    $indexMethodView = 'index_method';
                }
                $variables['renderType'] = 'paginate(10)';
                break;

            case 'datatables':
                $indexMethodView = 'index_method_datatable';
                $this->generateDataTable();
                break;

            case 'livewire':
                $indexMethodView = 'index_method_livewire';
                $this->generateLivewireTable();
                break;

            default:
                throw new Exception('Invalid Table Type');
        }

        if ($this->config->options->repositoryPattern) {
            $viewName = 'controller_repository';
        } else {
            $viewName = 'controller';
        }

        $variables['indexMethod'] = view('laravel-generator::scaffold.controller.'.$indexMethodView, $variables)
            ->render();

        $templateData = view('laravel-generator::scaffold.controller.'.$viewName, $variables)->render();

        g_filesystem()->createFile($this->path.$this->fileName, $templateData);

        $this->config->commandComment(infy_nl().'Controller created: ');
        $this->config->commandInfo($this->fileName);
    }

    protected function generateDataTable()
    {
        $templateData = view('laravel-generator::scaffold.table.datatable', [
            'columns' => implode(','.infy_nl_tab(1, 3), $this->generateDataTableColumns()),
        ])->render();

        $path = $this->config->paths->dataTables;

        $fileName = $this->config->modelNames->name.'DataTable.php';

        g_filesystem()->createFile($path.$fileName, $templateData);

        $this->config->commandComment(infy_nl().'DataTable created: ');
        $this->config->commandInfo($fileName);
    }

    protected function generateLivewireTable()
    {
        $templateData = view('laravel-generator::scaffold.table.livewire', [
            'columns' => implode(','.infy_nl_tab(1, 3), $this->generateLivewireTableColumns()),
        ])->render();

        $path = $this->config->paths->livewireTables;

        $fileName = $this->config->modelNames->plural.'Table.php';

        g_filesystem()->createFile($path.$fileName, $templateData);

        $this->config->commandComment(infy_nl().'LivewireTable created: ');
        $this->config->commandInfo($fileName);
    }

    protected function generateDataTableColumns(): array
    {
        $dataTableColumns = [];
        foreach ($this->config->fields as $field) {
            if (!$field->inIndex) {
                continue;
            }

            $dataTableColumns[] = trim(view(
                $this->templateType.'::templates.scaffold.table.datatable.column',
                $field->variables()
            )->render());
        }

        return $dataTableColumns;
    }

    protected function generateLivewireTableColumns(): array
    {
        $livewireTableColumns = [];

        foreach ($this->config->fields as $field) {
            if (!$field->inIndex) {
                continue;
            }

            $fieldTemplate = 'Column::make("'.$field->getTitle().'", "'.$field->name.'")'.infy_nl();
            $fieldTemplate .= infy_tabs(4).'->sortable()';

            if ($field->isSearchable) {
                $fieldTemplate .= infy_nl().infy_tabs(4).'->searchable()';
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

        if ($this->config->tableType === 'livewire') {
            if ($this->rollbackFile(
                $this->config->paths->livewireTables,
                $this->config->modelNames->plural.'Table.php'
            )) {
                $this->config->commandComment('Livewire Table file deleted: '.$this->fileName);
            }
        }
    }
}

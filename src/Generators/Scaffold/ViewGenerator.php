<?php

namespace InfyOm\Generator\Generators\Scaffold;

use Exception;
use Illuminate\Support\Str;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Generators\ViewServiceProviderGenerator;
use InfyOm\Generator\Utils\HTMLFieldGenerator;

class ViewGenerator extends BaseGenerator
{
    private string $templateType;
    private string $templateViewPath;

    public function __construct()
    {
        parent::__construct();

        $this->path = $this->config->paths->views;
        $this->templateType = config('laravel_generator.templates', 'adminlte-templates');
        $this->templateViewPath = $this->templateType . '::templates';
    }

    public function generate()
    {
        if (!file_exists($this->path)) {
            mkdir($this->path, 0755, true);
        }

//        $htmlInputs = Arr::pluck($this->config->fields, 'htmlInput');

        //TODO: Manage files
//        if (in_array('file', $htmlInputs)) {
//            $this->config->addDynamicVariable('$FILES$', ", 'files' => true");
//        }

        $this->config->commandComment(PHP_EOL . 'Generating Views...');

        if ($this->config->getOption('views')) {
            $viewsToBeGenerated = explode(',', $this->config->getOption('views'));

            if (in_array('index', $viewsToBeGenerated)) {
                $this->generateTable();
                $this->generateIndex();
            }

            if (count(array_intersect(['create', 'update'], $viewsToBeGenerated)) > 0) {
                $this->generateFields();
            }

            if (in_array('create', $viewsToBeGenerated)) {
                $this->generateCreate();
            }

            if (in_array('edit', $viewsToBeGenerated)) {
                $this->generateUpdate();
            }

            if (in_array('show', $viewsToBeGenerated)) {
                $this->generateShowFields();
                $this->generateShow();
            }
        } else {
            $this->generateTable();
            $this->generateIndex();
            $this->generateFields();
            $this->generateCreate();
            $this->generateUpdate();
            $this->generateShowFields();
            $this->generateShow();
        }

        $this->config->commandComment('Views created: ');
    }

    private function generateTable()
    {
        if ($this->config->tableType === 'livewire') {
            return;
        }

        switch ($this->config->tableType) {
            case 'blade':
                $templateData = $this->generateBladeTableBody();
                break;

            case 'datatables':
                $templateData = $this->generateDataTableBody();
                $this->generateDataTableActions();
                break;

            default:
                throw new Exception('Invalid Table Type');
        }

        g_filesystem()->createFile($this->path . 'table.blade.php', $templateData);

        $this->config->commandInfo('table.blade.php created');
    }

    private function generateDataTableBody(): string
    {
        return view($this->templateViewPath.'.scaffold.table.datatable.body')->render();
    }

    private function generateDataTableActions()
    {
        $templateData = view($this->templateViewPath.'.scaffold.table.datatable.actions')->render();

        g_filesystem()->createFile($this->path . 'datatables_actions.blade.php', $templateData);

        $this->config->commandInfo('datatables_actions.blade.php created');
    }

    private function generateBladeTableBody(): string
    {
        $tableBodyFields = [];

        foreach ($this->config->fields as $field) {
            if (!$field->inIndex) {
                continue;
            }

            $tableBodyFields[] = view($this->templateViewPath.'.scaffold.table.blade.cell', [
                'modelVariable' => $this->config->modelNames->camel,
                'fieldName' => $field->name,
            ])->render();
        }

        $tableBodyFields = implode(infy_nl_tab(1, 5), $tableBodyFields);

        $paginate = view($this->templateViewPath.'.scaffold.paginate')->render();

        return view($this->templateViewPath . '.scaffold.table.blade.body', [
            'fieldHeaders' => $this->generateTableHeaderFields(),
            'fieldBody' => $tableBodyFields,
            'paginate' => $paginate
        ])->render();
    }

    private function generateTableHeaderFields(): string
    {
        $headerFields = [];

        foreach ($this->config->fields as $field) {
            if (!$field->inIndex) {
                continue;
            }

            if ($this->config->options->localized) {
                /**
                 * Replacing $FIELD_NAME$ before fill_template_with_field_data_locale() otherwise also
                 * $FIELD_NAME$ get replaced with @lang('models/$modelName.fields.$value')
                 * and so we don't have $FIELD_NAME$ in table_header_locale.stub
                 * We could need 'raw' field name in header for example for sorting.
                 * We still have $FIELD_NAME_TITLE$ replaced with @lang('models/$modelName.fields.$value').
                 *
                 * @see issue https://github.com/InfyOmLabs/laravel-generator/issues/887
                 */
                $preFilledHeaderFieldTemplate = str_replace('$FIELD_NAME$', $field->name, '');

                $headerFields[] = fill_template_with_field_data_locale(
                    $this->config->dynamicVars,
                    ['$FIELD_NAME_TITLE$' => 'fieldTitle', '$FIELD_NAME$' => 'name'],
                    $preFilledHeaderFieldTemplate,
                    $field
                );
            } else {
                $headerFields[] = view(
                    $this->templateType . '::templates.scaffold.table.blade.header',
                    $field->variables()
                )->render();
            }
        }

        return implode(infy_nl_tab(1, 4), $headerFields);
    }

    private function generateIndex()
    {
        switch ($this->config->tableType) {
            case 'datatables':
            case 'blade':
                $tableReplaceString = "@include('".$this->config->modelNames->snakePlural.".table')";
                break;

            case 'livewire':
                $tableReplaceString = view($this->templateViewPath.'.scaffold.table.livewire.body')->render();
                break;

            default:
                throw new Exception('Invalid table type');
        }

        $templateData = view($this->templateViewPath.'.scaffold.index', ['table' => $tableReplaceString])
            ->render();

        g_filesystem()->createFile($this->path . 'index.blade.php', $templateData);

        $this->config->commandInfo('index.blade.php created');
    }

    private function generateFields()
    {
        $htmlFields = [];

        foreach ($this->config->fields as $field) {
            if (!$field->inForm) {
                continue;
            }

            $validations = explode('|', $field->validations);
            $minMaxRules = '';
            foreach ($validations as $validation) {
                if (!Str::contains($validation, ['max:', 'min:'])) {
                    continue;
                }

                $validationText = substr($validation, 0, 3);
                $sizeInNumber = substr($validation, 4);

                $sizeText = ($validationText == 'min') ? 'minlength' : 'maxlength';
                if ($field->htmlType == 'number') {
                    $sizeText = $validationText;
                }

                $size = ",'$sizeText' => $sizeInNumber";
                $minMaxRules .= $size;
            }
            // TODO:
//            $this->config->addDynamicVariable('$SIZE$', $minMaxRules);

            $htmlFields[] = HTMLFieldGenerator::generateHTML(
                $field,
                $this->templateViewPath
            );

            // TODO
//            if ($field->htmlType == 'selectTable') {
//                $inputArr = explode(',', $field->htmlValues[1]);
//                $columns = '';
//                foreach ($inputArr as $item) {
//                    $columns .= "'$item'" . ',';  //e.g 'email,id,'
//                }
//                $columns = substr_replace($columns, '', -1); // remove last ,
//
//                $htmlValues = explode(',', $field->htmlValues[0]);
//                $selectTable = $htmlValues[0];
//                $modalName = null;
//                if (count($htmlValues) == 2) {
//                    $modalName = $htmlValues[1];
//                }
//
//                $tableName = $this->config->tableName;
//                $viewPath = $this->config->prefixes->view;
//                if (!empty($viewPath)) {
//                    $tableName = $viewPath . '.' . $tableName;
//                }
//
//                $variableName = Str::singular($selectTable) . 'Items'; // e.g $userItems
//
//                $fieldTemplate = $this->generateViewComposer($tableName, $variableName, $columns, $selectTable, $modalName);
//            }
        }

        g_filesystem()->createFile($this->path . 'fields.blade.php', implode(infy_nls(2), $htmlFields));
        $this->config->commandInfo('field.blade.php created');
    }

    private function generateViewComposer($tableName, $variableName, $columns, $selectTable, $modelName = null): string
    {
        $templateName = 'scaffold.fields.select';
        if ($this->config->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }
        $fieldTemplate = get_template($templateName, $this->templateType);

        $viewServiceProvider = new ViewServiceProviderGenerator();
        $viewServiceProvider->generate();
        $viewServiceProvider->addViewVariables($tableName . '.fields', $variableName, $columns, $selectTable, $modelName);

        return str_replace(
            '$INPUT_ARR$',
            '$' . $variableName,
            $fieldTemplate
        );
    }

    private function generateCreate()
    {
        $templateData = view($this->templateViewPath.'.scaffold.create')->render();

        g_filesystem()->createFile($this->path . 'create.blade.php', $templateData);
        $this->config->commandInfo('create.blade.php created');
    }

    private function generateUpdate()
    {
        $templateData = view($this->templateViewPath.'.scaffold.edit')->render();

        g_filesystem()->createFile($this->path . 'edit.blade.php', $templateData);
        $this->config->commandInfo('edit.blade.php created');
    }

    private function generateShowFields()
    {
        $fieldsStr = '';

        foreach ($this->config->fields as $field) {
            if (!$field->inView) {
                continue;
            }

            $fieldsStr .= view($this->templateViewPath.'.scaffold.show_field', $field->variables());
            $fieldsStr .= PHP_EOL.PHP_EOL;
        }

        g_filesystem()->createFile($this->path . 'show_fields.blade.php', $fieldsStr);
        $this->config->commandInfo('show_fields.blade.php created');
    }

    private function generateShow()
    {
        $templateData = view($this->templateViewPath.'.scaffold.show')->render();

        g_filesystem()->createFile($this->path . 'show.blade.php', $templateData);
        $this->config->commandInfo('show.blade.php created');
    }

    public function rollback($views = [])
    {
        $files = [
            'table.blade.php',
            'index.blade.php',
            'fields.blade.php',
            'create.blade.php',
            'edit.blade.php',
            'show.blade.php',
            'show_fields.blade.php',
        ];

        if (!empty($views)) {
            $files = [];
            foreach ($views as $view) {
                $files[] = $view . '.blade.php';
            }
        }

        if ($this->config->tableType === 'datatables') {
            $files[] = 'datatables_actions.blade.php';
        }

        foreach ($files as $file) {
            if ($this->rollbackFile($this->path, $file)) {
                $this->config->commandComment($file . ' file deleted');
            }
        }
    }
}

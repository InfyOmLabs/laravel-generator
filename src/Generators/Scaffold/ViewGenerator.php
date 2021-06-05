<?php

namespace InfyOm\Generator\Generators\Scaffold;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Generators\ViewServiceProviderGenerator;
use InfyOm\Generator\Utils\FileUtil;
use InfyOm\Generator\Utils\HTMLFieldGenerator;

class ViewGenerator extends BaseGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    /** @var string */
    private $templateType;

    /** @var array */
    private $htmlFields;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathViews;
        $this->templateType = config('infyom.laravel_generator.templates', 'adminlte-templates');
    }

    public function generate()
    {
        if (!file_exists($this->path)) {
            mkdir($this->path, 0755, true);
        }

        $htmlInputs = Arr::pluck($this->commandData->fields, 'htmlInput');
        if (in_array('file', $htmlInputs)) {
            $this->commandData->addDynamicVariable('$FILES$', ", 'files' => true");
        }

        $this->commandData->commandComment("\nGenerating Views...");

        if ($this->commandData->getOption('views')) {
            $viewsToBeGenerated = explode(',', $this->commandData->getOption('views'));

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

        $this->commandData->commandComment('Views created: ');
    }

    private function generateTable()
    {
        if ($this->commandData->getAddOn('datatables')) {
            $templateData = $this->generateDataTableBody();
            $this->generateDataTableActions();
        } else {
            $templateData = $this->generateBladeTableBody();
        }

        FileUtil::createFile($this->path, 'table.blade.php', $templateData);

        $this->commandData->commandInfo('table.blade.php created');
    }

    private function generateDataTableBody()
    {
        $templateData = get_template('scaffold.views.datatable_body', $this->templateType);

        return fill_template($this->commandData->dynamicVars, $templateData);
    }

    private function generateDataTableActions()
    {
        $templateName = 'datatables_actions';

        if ($this->commandData->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }

        $templateData = get_template('scaffold.views.'.$templateName, $this->templateType);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, 'datatables_actions.blade.php', $templateData);

        $this->commandData->commandInfo('datatables_actions.blade.php created');
    }

    private function generateBladeTableBody()
    {
        $templateName = 'blade_table_body';

        $tableFields = $this->generateTableHeaderFields();
        if ($this->commandData->jqueryDT()) {
            $templateName = 'js_table';
            $tableFields = $this->generateJSTableHeaderFields();
        }

        if ($this->commandData->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }

        $templateData = get_template('scaffold.views.'.$templateName, $this->templateType);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $templateData = str_replace('$FIELD_HEADERS$', $tableFields, $templateData);

        $cellFieldTemplate = get_template('scaffold.views.table_cell', $this->templateType);

        $tableBodyFields = [];

        foreach ($this->commandData->fields as $field) {
            if (!$field->inIndex) {
                continue;
            }

            $tableBodyFields[] = fill_template_with_field_data(
                $this->commandData->dynamicVars,
                $this->commandData->fieldNamesMapping,
                $cellFieldTemplate,
                $field
            );
        }

        $tableBodyFields = implode(infy_nl_tab(1, 3), $tableBodyFields);

        return str_replace('$FIELD_BODY$', $tableBodyFields, $templateData);
    }

    private function generateJSTableHeaderFields()
    {
        $fields = '';
        foreach ($this->commandData->fields as $field) {
            if (in_array($field->name, ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }

            $fields .= '<th scope="col">'.str_replace("'", '', $field->name).'</th>';
        }

        return $fields;
    }

    private function generateTableHeaderFields()
    {
        $templateName = 'table_header';

        $localized = false;
        if ($this->commandData->isLocalizedTemplates()) {
            $templateName .= '_locale';
            $localized = true;
        }

        $headerFieldTemplate = get_template('scaffold.views.'.$templateName, $this->templateType);

        $headerFields = [];

        foreach ($this->commandData->fields as $field) {
            if (!$field->inIndex) {
                continue;
            }

            if ($localized) {
                /**
                 * Replacing $FIELD_NAME$ before fill_template_with_field_data_locale() otherwise also
                 * $FIELD_NAME$ get replaced with @lang('models/$modelName.fields.$value')
                 * and so we don't have $FIELD_NAME$ in table_header_locale.stub
                 * We could need 'raw' field name in header for example for sorting.
                 * We still have $FIELD_NAME_TITLE$ replaced with @lang('models/$modelName.fields.$value').
                 *
                 * @see issue https://github.com/InfyOmLabs/laravel-generator/issues/887
                 */
                $preFilledHeaderFieldTemplate = str_replace('$FIELD_NAME$', $field->name, $headerFieldTemplate);

                $headerFields[] = $fieldTemplate = fill_template_with_field_data_locale(
                    $this->commandData->dynamicVars,
                    $this->commandData->fieldNamesMapping,
                    $preFilledHeaderFieldTemplate,
                    $field
                );
            } else {
                $headerFields[] = $fieldTemplate = fill_template_with_field_data(
                    $this->commandData->dynamicVars,
                    $this->commandData->fieldNamesMapping,
                    $headerFieldTemplate,
                    $field
                );
            }
        }

        return implode(infy_nl_tab(1, 2), $headerFields);
    }

    private function generateIndex()
    {
        $templateName = ($this->commandData->jqueryDT()) ? 'js_index' : 'index';

        if ($this->commandData->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }

        $templateData = get_template('scaffold.views.'.$templateName, $this->templateType);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        if ($this->commandData->getAddOn('datatables')) {
            $templateData = str_replace('$PAGINATE$', '', $templateData);
        } else {
            $paginate = $this->commandData->getOption('paginate');

            if ($paginate) {
                $paginateTemplate = get_template('scaffold.views.paginate', $this->templateType);

                $paginateTemplate = fill_template($this->commandData->dynamicVars, $paginateTemplate);

                $templateData = str_replace('$PAGINATE$', $paginateTemplate, $templateData);
            } else {
                $templateData = str_replace('$PAGINATE$', '', $templateData);
            }
        }

        FileUtil::createFile($this->path, 'index.blade.php', $templateData);

        $this->commandData->commandInfo('index.blade.php created');
    }

    private function generateFields()
    {
        $templateName = 'fields';

        $localized = false;
        if ($this->commandData->isLocalizedTemplates()) {
            $templateName .= '_locale';
            $localized = true;
        }

        $this->htmlFields = [];

        foreach ($this->commandData->fields as $field) {
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
            $this->commandData->addDynamicVariable('$SIZE$', $minMaxRules);

            $fieldTemplate = HTMLFieldGenerator::generateHTML($field, $this->templateType, $localized);

            if ($field->htmlType == 'selectTable') {
                $inputArr = explode(',', $field->htmlValues[1]);
                $columns = '';
                foreach ($inputArr as $item) {
                    $columns .= "'$item'".',';  //e.g 'email,id,'
                }
                $columns = substr_replace($columns, '', -1); // remove last ,

                $htmlValues = explode(',', $field->htmlValues[0]);
                $selectTable = $htmlValues[0];
                $modalName = null;
                if (count($htmlValues) == 2) {
                    $modalName = $htmlValues[1];
                }

                $tableName = $this->commandData->config->tableName;
                $viewPath = $this->commandData->config->prefixes['view'];
                if (!empty($viewPath)) {
                    $tableName = $viewPath.'.'.$tableName;
                }

                $variableName = Str::singular($selectTable).'Items'; // e.g $userItems

                $fieldTemplate = $this->generateViewComposer($tableName, $variableName, $columns, $selectTable, $modalName);
            }

            if (!empty($fieldTemplate)) {
                $fieldTemplate = fill_template_with_field_data(
                    $this->commandData->dynamicVars,
                    $this->commandData->fieldNamesMapping,
                    $fieldTemplate,
                    $field
                );
                $this->htmlFields[] = $fieldTemplate;
            }
        }

        $templateData = get_template('scaffold.views.'.$templateName, $this->templateType);
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $templateData = str_replace('$FIELDS$', implode("\n\n", $this->htmlFields), $templateData);

        FileUtil::createFile($this->path, 'fields.blade.php', $templateData);
        $this->commandData->commandInfo('field.blade.php created');
    }

    private function generateViewComposer($tableName, $variableName, $columns, $selectTable, $modelName = null)
    {
        $templateName = 'scaffold.fields.select';
        if ($this->commandData->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }
        $fieldTemplate = get_template($templateName, $this->templateType);

        $viewServiceProvider = new ViewServiceProviderGenerator($this->commandData);
        $viewServiceProvider->generate();
        $viewServiceProvider->addViewVariables($tableName.'.fields', $variableName, $columns, $selectTable, $modelName);

        $fieldTemplate = str_replace(
            '$INPUT_ARR$',
            '$'.$variableName,
            $fieldTemplate
        );

        return $fieldTemplate;
    }

    private function generateCreate()
    {
        $templateName = 'create';

        if ($this->commandData->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }

        $templateData = get_template('scaffold.views.'.$templateName, $this->templateType);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, 'create.blade.php', $templateData);
        $this->commandData->commandInfo('create.blade.php created');
    }

    private function generateUpdate()
    {
        $templateName = 'edit';

        if ($this->commandData->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }

        $templateData = get_template('scaffold.views.'.$templateName, $this->templateType);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, 'edit.blade.php', $templateData);
        $this->commandData->commandInfo('edit.blade.php created');
    }

    private function generateShowFields()
    {
        $templateName = 'show_field';
        if ($this->commandData->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }
        $fieldTemplate = get_template('scaffold.views.'.$templateName, $this->templateType);

        $fieldsStr = '';

        foreach ($this->commandData->fields as $field) {
            if (!$field->inView) {
                continue;
            }
            $singleFieldStr = str_replace(
                '$FIELD_NAME_TITLE$',
                Str::title(str_replace('_', ' ', $field->name)),
                $fieldTemplate
            );
            $singleFieldStr = str_replace('$FIELD_NAME$', $field->name, $singleFieldStr);
            $singleFieldStr = fill_template($this->commandData->dynamicVars, $singleFieldStr);

            $fieldsStr .= $singleFieldStr."\n\n";
        }

        FileUtil::createFile($this->path, 'show_fields.blade.php', $fieldsStr);
        $this->commandData->commandInfo('show_fields.blade.php created');
    }

    private function generateShow()
    {
        $templateName = 'show';

        if ($this->commandData->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }

        $templateData = get_template('scaffold.views.'.$templateName, $this->templateType);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, 'show.blade.php', $templateData);
        $this->commandData->commandInfo('show.blade.php created');
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
                $files[] = $view.'.blade.php';
            }
        }

        if ($this->commandData->getAddOn('datatables')) {
            $files[] = 'datatables_actions.blade.php';
        }

        foreach ($files as $file) {
            if ($this->rollbackFile($this->path, $file)) {
                $this->commandData->commandComment($file.' file deleted');
            }
        }
    }
}

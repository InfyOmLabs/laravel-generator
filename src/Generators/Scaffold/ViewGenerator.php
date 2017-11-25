<?php

namespace InfyOm\Generator\Generators\Scaffold;

use Illuminate\Support\Str;
use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\FileUtil;
use InfyOm\Generator\Utils\GeneratorFieldsInputUtil;
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
        $templateData = get_template('scaffold.views.datatables_actions', $this->templateType);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, 'datatables_actions.blade.php', $templateData);

        $this->commandData->commandInfo('datatables_actions.blade.php created');
    }

    private function generateBladeTableBody()
    {
        $templateData = get_template('scaffold.views.blade_table_body', $this->templateType);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $templateData = str_replace('$FIELD_HEADERS$', $this->generateTableHeaderFields(), $templateData);

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

    private function generateTableHeaderFields()
    {
        $headerFieldTemplate = get_template('scaffold.views.table_header', $this->templateType);

        $headerFields = [];

        foreach ($this->commandData->fields as $field) {
            if (!$field->inIndex) {
                continue;
            }
            $headerFields[] = $fieldTemplate = fill_template_with_field_data(
                $this->commandData->dynamicVars,
                $this->commandData->fieldNamesMapping,
                $headerFieldTemplate,
                $field
            );
        }

        return implode(infy_nl_tab(1, 2), $headerFields);
    }

    private function generateIndex()
    {
        $templateData = get_template('scaffold.views.index', $this->templateType);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        if ($this->commandData->getOption('datatables')) {
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
        $this->htmlFields = [];

        foreach ($this->commandData->fields as $field) {
            if (!$field->inForm) {
                continue;
            }

            //            switch ($field->htmlType) {
            //                case 'text':
            //                case 'textarea':
            //                case 'date':
            //                case 'file':
            //                case 'email':
            //                case 'password':
            //                case 'number':
            //                    $fieldTemplate = get_template('scaffold.fields.' . $field->htmlType, $this->templateType);
            //                    break;
            //
            //                case 'select':
            //                case 'enum':
            //                    $fieldTemplate = get_template('scaffold.fields.select', $this->templateType);
            //                    $inputsArr = explode(',', $field['htmlTypeInputs']);
            //
            //                    $fieldTemplate = str_replace(
            //                        '$INPUT_ARR$',
            //                        GeneratorFieldsInputUtil::prepareKeyValueArrayStr($inputsArr),
            //                        $fieldTemplate
            //                    );
            //                    break;
            //
            //                case 'radio':
            //                    $fieldTemplate = get_template('scaffold.fields.radio_group', $this->templateType);
            //                    $radioTemplate = get_template('scaffold.fields.radio', $this->templateType);
            //                    $inputsArr = explode(',', $field['htmlTypeInputs']);
            //                    $radioButtons = [];
            //                    foreach ($inputsArr as $item) {
            //                        $radioButtonsTemplate = fill_field_template(
            //                            $this->commandData->fieldNamesMapping,
            //                            $radioTemplate, $field
            //                        );
            //                        $radioButtonsTemplate = str_replace('$VALUE$', $item, $radioButtonsTemplate);
            //                        $radioButtons[] = $radioButtonsTemplate;
            //                    }
            //                    $fieldTemplate = str_replace('$RADIO_BUTTONS$', implode("\n", $radioButtons), $fieldTemplate);
            //                    break;
            //
            ////                case 'checkbox-group':
            ////                    $fieldTemplate = get_template('scaffold.fields.checkbox_group', $this->templateType);
            ////                      $radioTemplate = get_template('scaffold.fields.checks', $this->templateType);
            ////                      $inputsArr = explode(',', $field['htmlTypeInputs']);
            ////                      $radioButtons = [];
            ////                      foreach ($inputsArr as $item) {
            ////                          $radioButtonsTemplate = fill_field_template(
            ////                              $this->commandData->fieldNamesMapping,
            ////                              $radioTemplate,
            ////                              $field
            ////                          );
            ////                          $radioButtonsTemplate = str_replace('$VALUE$', $item, $radioButtonsTemplate);
            ////                          $radioButtons[] = $radioButtonsTemplate;
            ////                      }
            ////                    $fieldTemplate = str_replace('$CHECKBOXES$', implode("\n", $radioButtons), $fieldTemplate);
            ////                    break;
            //
            //                case 'bool-checkbox':
            //                    $fieldTemplate = get_template('scaffold.fields.bool-checkbox', $this->templateType);
            //                    $checkboxValue = $value = $field['htmlTypeInputs'];
            //                    if ($field['fieldType'] === 'boolean') {
            //                        if ($checkboxValue === 'checked') {
            //                            $checkboxValue = '1, true';
            //                        } elseif ($checkboxValue === 'unchecked') {
            //                            $checkboxValue = '0';
            //                        }
            //                    }
            //                    $fieldTemplate = str_replace('$CHECKBOX_VALUE$', $checkboxValue, $fieldTemplate);
            //                    $fieldTemplate = str_replace('$VALUE$', $value, $fieldTemplate);
            //                    break;
            //
            //                case 'toggle-switch':
            //                    $fieldTemplate = get_template('scaffold.fields.toggle-switch', $this->templateType);
            //                    $checkboxValue = $value = $field['htmlTypeInputs'];
            //                    if ($field['fieldType'] === 'boolean') {
            //                        $checkboxValue = "[ 'On' => '1' , 'Off' => '0']";
            //                    }
            //                    $fieldTemplate = str_replace('$CHECKBOX_VALUE$', $checkboxValue, $fieldTemplate);
            //                    //$fieldTemplate = str_replace('$VALUE$', $value, $fieldTemplate);
            //                    break;
            //
            //                case 'checkbox':
            //                    $fieldTemplate = get_template('scaffold.fields.checkbox', $this->templateType);
            //                    $checkboxValue = $value = $field['htmlTypeInputs'];
            //                    if ($field['fieldType'] != 'boolean') {
            //                        $checkboxValue = "'" . $value . "'";
            //                    }
            //                    $fieldTemplate = str_replace('$CHECKBOX_VALUE$', $checkboxValue, $fieldTemplate);
            //                    $fieldTemplate = str_replace('$VALUE$', $value, $fieldTemplate);
            //                    break;
            //
            //                case 'boolean':
            //                    $fieldTemplate = get_template('scaffold.fields.boolean', $this->templateType);
            //                    $checkboxValue = $value = $field['htmlTypeInputs'];
            //                    if ($field['fieldType'] == 'boolean') {
            //                        $checkboxValue = true;
            //                    }
            //                    $fieldTemplate = str_replace('$CHECKBOX_VALUE$', $checkboxValue, $fieldTemplate);
            //                    // $fieldTemplate = str_replace('$VALUE$', $value, $fieldTemplate);
            //                    break;
            //
            //                default:
            //                    $fieldTemplate = '';
            //                    break;
            //            }

            $fieldTemplate = HTMLFieldGenerator::generateHTML($field, $this->templateType);

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

        $templateData = get_template('scaffold.views.fields', $this->templateType);
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $templateData = str_replace('$FIELDS$', implode("\n\n", $this->htmlFields), $templateData);

        FileUtil::createFile($this->path, 'fields.blade.php', $templateData);
        $this->commandData->commandInfo('field.blade.php created');
    }

    private function generateCreate()
    {
        $templateData = get_template('scaffold.views.create', $this->templateType);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, 'create.blade.php', $templateData);
        $this->commandData->commandInfo('create.blade.php created');
    }

    private function generateUpdate()
    {
        $templateData = get_template('scaffold.views.edit', $this->templateType);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, 'edit.blade.php', $templateData);
        $this->commandData->commandInfo('edit.blade.php created');
    }

    private function generateShowFields()
    {
        $fieldTemplate = get_template('scaffold.views.show_field', $this->templateType);

        $fieldsStr = '';

        foreach ($this->commandData->fields as $field) {
            $singleFieldStr = str_replace('$FIELD_NAME_TITLE$', Str::title(str_replace('_', ' ', $field->name)),
                $fieldTemplate);
            $singleFieldStr = str_replace('$FIELD_NAME$', $field->name, $singleFieldStr);
            $singleFieldStr = fill_template($this->commandData->dynamicVars, $singleFieldStr);

            $fieldsStr .= $singleFieldStr."\n\n";
        }

        FileUtil::createFile($this->path, 'show_fields.blade.php', $fieldsStr);
        $this->commandData->commandInfo('show_fields.blade.php created');
    }

    private function generateShow()
    {
        $templateData = get_template('scaffold.views.show', $this->templateType);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, 'show.blade.php', $templateData);
        $this->commandData->commandInfo('show.blade.php created');
    }

    public function rollback()
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

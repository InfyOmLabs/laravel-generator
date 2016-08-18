<?php

namespace InfyOm\Generator\Generators\VueJs;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\FileUtil;
use InfyOm\Generator\Utils\GeneratorFieldsInputUtil;

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
        $this->templateType = config('infyom.laravel_generator.templates', 'core-templates');
    }

    public function generate()
    {
        if (!file_exists($this->path)) {
            mkdir($this->path, 0755, true);
        }

        $this->commandData->commandComment("\nGenerating Views...");
        $this->generateTable();
        $this->generateIndex();
        $this->generateFields();
        $this->generateForm();
        $this->generateShow();
        $this->generateDelete();
        $this->commandData->commandComment('Views created: ');
    }

    private function generateTable()
    {
        $templateData = $this->generateBladeTableBody();

        FileUtil::createFile($this->path, 'table.blade.php', $templateData);

        $this->commandData->commandInfo('table.blade.php created');
    }

    private function generateBladeTableBody()
    {
        $templateData = get_template('vuejs.views.blade_table_body', $this->templateType);
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        return $templateData;
    }

    private function generateIndex()
    {
        $templateData = get_template('vuejs.views.index', $this->templateType);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        if ($this->commandData->getOption('datatables')) {
            $templateData = str_replace('$PAGINATE$', '', $templateData);
        } else {
            $paginate = $this->commandData->getOption('paginate');

            if ($paginate) {
                $paginateTemplate = get_template('vuejs.views.paginate', $this->templateType);

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
            switch ($field->htmlType) {
                case 'text':
                case 'textarea':
                case 'date':
                case 'file':
                case 'email':
                case 'password':
                case 'number':
                    $fieldTemplate = get_template('vuejs.fields.'.$field->htmlType, $this->templateType);
                    break;

                case 'select':
                case 'enum':
                    $fieldTemplate = get_template('vuejs.fields.select', $this->templateType);
                    $inputsArr = explode(',', $field['htmlTypeInputs']);

                    $fieldTemplate = str_replace(
                        '$INPUT_ARR$',
                        GeneratorFieldsInputUtil::prepareKeyValueArrayStr($inputsArr),
                        $fieldTemplate
                    );
                    break;

                case 'radio':
                    $fieldTemplate = get_template('vuejs.fields.radio_group', $this->templateType);
                    $radioTemplate = get_template('vuejs.fields.radio', $this->templateType);
                    $inputsArr = explode(',', $field['htmlTypeInputs']);
                    $radioButtons = [];
                    foreach ($inputsArr as $item) {
                        $radioButtonsTemplate = fill_field_template(
                            $this->commandData->fieldNamesMapping,
                            $radioTemplate, $field
                        );
                        $radioButtonsTemplate = str_replace('$VALUE$', $item, $radioButtonsTemplate);
                        $radioButtons[] = $radioButtonsTemplate;
                    }
                    $fieldTemplate = str_replace('$RADIO_BUTTONS$', implode("\n", $radioButtons), $fieldTemplate);
                    break;

//                case 'checkbox-group':
//                    $fieldTemplate = get_template('vuejs.fields.checkbox_group', $this->templateType);
//                      $radioTemplate = get_template('vuejs.fields.checks', $this->templateType);
//                      $inputsArr = explode(',', $field['htmlTypeInputs']);
//                      $radioButtons = [];
//                      foreach ($inputsArr as $item) {
//                          $radioButtonsTemplate = fill_field_template(
//                              $this->commandData->fieldNamesMapping,
//                              $radioTemplate,
//                              $field
//                          );
//                          $radioButtonsTemplate = str_replace('$VALUE$', $item, $radioButtonsTemplate);
//                          $radioButtons[] = $radioButtonsTemplate;
//                      }
//                    $fieldTemplate = str_replace('$CHECKBOXES$', implode("\n", $radioButtons), $fieldTemplate);
//                    break;

                case 'checkbox':
                    $fieldTemplate = get_template('vuejs.fields.checkbox', $this->templateType);
                    $checkboxValue = $value = $field['htmlTypeInputs'];
                    if ($field['fieldType'] != 'boolean') {
                        $checkboxValue = "'".$value."'";
                    }
                    $fieldTemplate = str_replace('$CHECKBOX_VALUE$', $checkboxValue, $fieldTemplate);
                    $fieldTemplate = str_replace('$VALUE$', $value, $fieldTemplate);
                    break;

                default:
                    $fieldTemplate = '';
                    break;
            }

            if (!empty($fieldTemplate)) {
                if (isset($field['validations']) && !empty($field['validations'])) {
                    $rules = explode('|', $field['validations']);
                    foreach ($rules as $key => $rule) {
                        if ($rule == 'required') {
                            $rule .= ': true';
                        } else {
                            $rule = explode(':', $rule);
                            $rule = implode(': ', $rule);
                        }
                        $rules[$key] = $rule;
                    }
                    $validationRules = implode(', ', $rules);
                    $fieldTemplate = str_replace('$VALIDATION_RULES$', $validationRules, $fieldTemplate);

                    $validationMessagesTemplate = get_template('vuejs.fields.validation_messages', $this->templateType);
                    $validationMessages = '';
                    foreach ($rules as $rule) {
                        $rule = explode(':', $rule)[0];
                        $validationMessages .= str_replace('$RULE$', $rule, $validationMessagesTemplate)."\n";
                    }
                    $fieldTemplate = str_replace('$VALIDATION_MESSAGES$', $validationMessages, $fieldTemplate);
                }

                $fieldTemplate = fill_field_template(
                    $this->commandData->fieldNamesMapping,
                    $fieldTemplate,
                    $field
                );
                $this->htmlFields[] = $fieldTemplate;
            }
        }

        $templateData = get_template('vuejs.views.fields', $this->templateType);
        //$templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $templateData = str_replace('$FIELDS$', implode("\n\n", $this->htmlFields), $templateData);

        FileUtil::createFile($this->path, 'fields.blade.php', $templateData);
        $this->commandData->commandInfo('fields.blade.php created');
    }

    private function generateForm()
    {
        $templateData = get_template('vuejs.views.form', $this->templateType);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, 'form.blade.php', $templateData);
        $this->commandData->commandInfo('form.blade.php created');
    }

    private function generateShow()
    {
        $templateData = get_template('vuejs.views.show', $this->templateType);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, 'show.blade.php', $templateData);
        $this->commandData->commandInfo('show.blade.php created');
    }

    private function generateDelete()
    {
        $templateData = get_template('vuejs.views.delete', $this->templateType);

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, 'delete.blade.php', $templateData);
        $this->commandData->commandInfo('delete.blade.php created');
    }

    public function rollback()
    {
        $files = [
            'table.blade.php',
            'index.blade.php',
            'fields.blade.php',
            'form.blade.php',
            'show.blade.php',
            'delete.blade.php',
        ];

        foreach ($files as $file) {
            if ($this->rollbackFile($this->path, $file)) {
                $this->commandData->commandComment($file.' file deleted');
            }
        }
    }
}

<?php

namespace InfyOm\Generator\Generators\Scaffold;

use Illuminate\Support\Str;
use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Utils\FileUtil;
use InfyOm\Generator\Utils\GeneratorFieldsInputUtil;
use InfyOm\Generator\Utils\TemplateUtil;

class ViewGenerator
{
    /** @var  CommandData */
    private $commandData;

    /** @var string */
    private $path;

    /** @var string */
    private $templateType;

    /** @var array */
    private $htmlFields;

    public function __construct($commandData)
    {
        $this->commandData = $commandData;
        $this->path = config('infyom.laravel_generator.path.views', base_path('resources/views/'));
        $this->path = $this->path.'/'.$this->commandData->modelNames['camelPlural'].'/';
        $this->templateType = config('infyom.laravel_generator.path.templates', 'core-templates');
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
        $this->generateCreate();
        $this->generateUpdate();
        $this->generateShowFields();
        $this->generateShow();
        $this->commandData->commandComment('Views created: ');
    }

    private function generateTable()
    {
        $templateData = TemplateUtil::getTemplate('scaffold.views.table', $this->templateType);

        $templateData = TemplateUtil::fillTemplate($this->commandData->dynamicVars, $templateData);

        $fileName = 'table.blade.php';

        $headerFields = [];

        foreach ($this->commandData->inputFields as $field) {
            $headerFields[] = '<th>'.$field['fieldTitle'].'</th>';
        }

        $headerFields = implode("\n\t\t\t", $headerFields);

        $templateData = str_replace('$FIELD_HEADERS$', $headerFields, $templateData);

        $tableBodyFields = [];

        foreach ($this->commandData->inputFields as $field) {
            $tableBodyFields[] = '<td>{!! $'.$this->commandData->modelNames['camel'].'->'.
                $field['fieldName'].' !!}</td>';
        }

        $tableBodyFields = implode("\n\t\t\t", $tableBodyFields);

        $templateData = str_replace('$FIELD_BODY$', $tableBodyFields, $templateData);

        FileUtil::createFile($this->path, $fileName, $templateData);

        $this->commandData->commandInfo('table.blade.php created');
    }

    private function generateIndex()
    {
        $templateData = TemplateUtil::getTemplate('scaffold.views.index', $this->templateType);

        $templateData = TemplateUtil::fillTemplate($this->commandData->dynamicVars, $templateData);

        $paginate = $this->commandData->getOption('paginate');

        if ($paginate) {
            $paginateTemplate = TemplateUtil::getTemplate('scaffold.views.paginate', $this->templateType);

            $paginateTemplate = TemplateUtil::fillTemplate($this->commandData->dynamicVars, $paginateTemplate);

            $templateData = str_replace('$PAGINATE$', $paginateTemplate, $templateData);
        } else {
            $templateData = str_replace('$PAGINATE$', '', $templateData);
        }

        $fileName = 'index.blade.php';

        FileUtil::createFile($this->path, $fileName, $templateData);

        $this->commandData->commandInfo('index.blade.php created');
    }

    private function generateFields()
    {
        $this->htmlFields = [];

        foreach ($this->commandData->inputFields as $field) {
            switch ($field['htmlType']) {
                case 'text':
                case 'textarea':
                case 'date':
                case 'file':
                case 'email':
                case 'password':
                case 'number':
                    $fieldTemplate = TemplateUtil::getTemplate('scaffold.fields.'.$field['htmlType'], $this->templateType);
                    break;

                case 'select':
                case 'enum':
                    $fieldTemplate = TemplateUtil::getTemplate('scaffold.fields.select', $this->templateType);
                    $inputsArr = explode(',', $field['htmlTypeInputs']);

                    $fieldTemplate = str_replace(
                        '$INPUT_ARR$',
                        GeneratorFieldsInputUtil::prepareKeyValueArrayStr($inputsArr),
                        $fieldTemplate
                    );
                    break;

                case 'radio':
                    $fieldTemplate = TemplateUtil::getTemplate('scaffold.fields.radio_group', $this->templateType);
                    $radioTemplate = TemplateUtil::getTemplate('scaffold.fields.radio', $this->templateType);
                    $inputsArr = explode(',', $field['htmlTypeInputs']);
                    $radioButtons = [];
                    foreach ($inputsArr as $item) {
                        $radioButtonsTemplate = TemplateUtil::fillFieldTemplate(
                            $this->commandData->fieldNamesMapping,
                            $radioTemplate, $field
                        );
                        $radioButtonsTemplate = str_replace('$VALUE$', $item, $radioButtonsTemplate);
                        $radioButtons[] = $radioButtonsTemplate;
                    }
                    $fieldTemplate = str_replace('$RADIO_BUTTONS$', implode("\n", $radioButtons), $fieldTemplate);
                    break;

                case 'checkbox':
                    $fieldTemplate = TemplateUtil::getTemplate('scaffold.fields.checkbox_group', $this->templateType);
                    $radioTemplate = TemplateUtil::getTemplate('scaffold.fields.checkbox', $this->templateType);
                    $inputsArr = explode(',', $field['htmlTypeInputs']);
                    $radioButtons = [];
                    foreach ($inputsArr as $item) {
                        $radioButtonsTemplate = TemplateUtil::fillFieldTemplate(
                            $this->commandData->fieldNamesMapping,
                            $radioTemplate,
                            $field
                        );
                        $radioButtonsTemplate = str_replace('$VALUE$', $item, $radioButtonsTemplate);
                        $radioButtons[] = $radioButtonsTemplate;
                    }
                    $fieldTemplate = str_replace('$CHECKBOXES$', implode("\n", $radioButtons), $fieldTemplate);
                    break;

                default:
                    $fieldTemplate = '';
                    break;
            }

            if (!empty($fieldTemplate)) {
                $fieldTemplate = TemplateUtil::fillFieldTemplate(
                    $this->commandData->fieldNamesMapping,
                    $fieldTemplate,
                    $field
                );
                $this->htmlFields[] = $fieldTemplate;
            }
        }

        $templateData = TemplateUtil::getTemplate('scaffold.views.fields', $this->templateType);
        $templateData = TemplateUtil::fillTemplate($this->commandData->dynamicVars, $templateData);

        $templateData = str_replace('$FIELDS$', implode("\n\n", $this->htmlFields), $templateData);

        FileUtil::createFile($this->path, 'fields.blade.php', $templateData);
        $this->commandData->commandInfo('field.blade.php created');
    }

    private function generateCreate()
    {
        $templateData = TemplateUtil::getTemplate('scaffold.views.create', $this->templateType);

        $templateData = TemplateUtil::fillTemplate($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, 'create.blade.php', $templateData);
        $this->commandData->commandInfo('create.blade.php created');
    }

    private function generateUpdate()
    {
        $templateData = TemplateUtil::getTemplate('scaffold.views.edit', $this->templateType);

        $templateData = TemplateUtil::fillTemplate($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, 'edit.blade.php', $templateData);
        $this->commandData->commandInfo('edit.blade.php created');
    }

    private function generateShow()
    {
        $templateData = TemplateUtil::getTemplate('scaffold.views.show', $this->templateType);

        $templateData = TemplateUtil::fillTemplate($this->commandData->dynamicVars, $templateData);

        $fileName = 'show.blade.php';

        FileUtil::createFile($this->path, $fileName, $templateData);
        $this->commandData->commandInfo('show.blade.php created');
    }

    private function generateShowFields()
    {
        $fieldTemplate = TemplateUtil::getTemplate('scaffold.views.show_field', $this->templateType);

        $fieldsStr = '';

        foreach ($this->commandData->inputFields as $field) {
            $singleFieldStr = str_replace('$FIELD_NAME_TITLE$', Str::title(str_replace('_', ' ', $field['fieldName'])), $fieldTemplate);
            $singleFieldStr = str_replace('$FIELD_NAME$', $field['fieldName'], $singleFieldStr);
            $singleFieldStr = TemplateUtil::fillTemplate($this->commandData->dynamicVars, $singleFieldStr);

            $fieldsStr .= $singleFieldStr."\n\n";
        }

        $fileName = 'show_fields.blade.php';

        FileUtil::createFile($this->path, $fileName, $fieldsStr);
        $this->commandData->commandInfo('show_fields.blade.php created');
    }
}

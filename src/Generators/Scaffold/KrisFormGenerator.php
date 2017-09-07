<?php

namespace InfyOm\Generator\Generators\Scaffold;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\FileUtil;

class KrisFormGenerator extends BaseGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    /** @var string */
    private $createFileName;

    /** @var string */
    private $updateFileName;

    /** @var string */
    private $FormFields;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->krisFormPath;
        $this->createFileName = 'Create' . $this->commandData->modelName . 'Form.php';
        $this->updateFileName = 'Update' . $this->commandData->modelName . 'Form.php';
        $this->showFileName = 'Show' . $this->commandData->modelName . 'Form.php';

    }

    public function generate()
    {
        $this->generateFormFields();
        $this->generateCreateForm();
        $this->generateUpdateForm();
        $this->generateShowForm();
    }

    private function generateFormFields()
    {

        $fieldarr = [];
        foreach ($this->commandData->fields as $field) {
            $_field = null;
            $_field[] ="'name' => '".$field->name. "'";
            $_field[] ="'type' => '".$field->htmlType. "'";
//            $fieldarr[] = "'options' => ['label' => 'trans('form.' . $field->name)']";

            if (empty($field->inForm)) {
                $_field[] = "'inform' => true";
            }else{
                $_field[] = "'inform' => ".($field->inForm ? 'true' : 'false');
            }
            $fieldarr[] = $_field;
        }
        $f_data= "";
        foreach ($fieldarr as $field) {
            $f_data .= "[" . infy_nl_tab(1, 3) . implode(',' . infy_nl_tab(1, 3), $field) . infy_nl_tab(1, 2) . "]," .infy_nl_tab(1, 2);
        }
        $this->FormFields = $f_data;
        return $fieldarr;
    }

    private function generateCreateForm()
    {
        $templateData = get_template('scaffold.krisform.create_form', 'laravel-generator');
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);
        $templateData = str_replace('$FIELDS_DATA$', $this->FormFields, $templateData);

        FileUtil::createFile($this->path, $this->createFileName, $templateData);

        $this->commandData->commandComment("\nCreate Form created: ");
        $this->commandData->commandInfo($this->createFileName);
    }

    private function generateUpdateForm()
    {
        $templateData = get_template('scaffold.krisform.update_form', 'laravel-generator');
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);
        $templateData = str_replace('$FIELDS_DATA$', $this->FormFields, $templateData);

        FileUtil::createFile($this->path, $this->updateFileName, $templateData);

        $this->commandData->commandComment("\nUpdate Form created: ");
        $this->commandData->commandInfo($this->updateFileName);
    }

    private function generateShowForm()
    {
        $templateData = get_template('scaffold.krisform.show_form', 'laravel-generator');

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);
        $templateData = str_replace('$FIELDS_DATA$', $this->FormFields, $templateData);
        FileUtil::createFile($this->path, $this->showFileName, $templateData);

        $this->commandData->commandComment("\nShow Form created: ");
        $this->commandData->commandInfo($this->showFileName);
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->createFileName)) {
            $this->commandData->commandComment('Create API Form file deleted: ' . $this->createFileName);
        }

        if ($this->rollbackFile($this->path, $this->updateFileName)) {
            $this->commandData->commandComment('Update API Form file deleted: ' . $this->updateFileName);
        }
        if ($this->rollbackFile($this->path, $this->showFileName)) {
            $this->commandData->commandComment('Update API Form file deleted: ' . $this->showFileName);
        }
    }
}

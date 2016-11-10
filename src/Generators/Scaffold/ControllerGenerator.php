<?php

namespace InfyOm\Generator\Generators\Scaffold;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\InfyOmHelpers;
use InfyOm\Generator\Utils\FileUtil;

class ControllerGenerator extends BaseGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    /** @var string */
    private $templateType;

    /** @var string */
    private $fileName;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathController;
        $this->templateType = config('infyom.laravel_generator.templates', 'core-templates');
        $this->fileName = $this->commandData->modelName.'Controller.php';
    }

    public function generate()
    {
        if ($this->commandData->getAddOn('datatables')) {
            $templateData = InfyOmHelpers::get_template('scaffold.controller.datatable_controller', 'laravel-generator');

            $this->generateDataTable();
        } else {
            $templateData = InfyOmHelpers::get_template('scaffold.controller.controller', 'laravel-generator');

            $paginate = $this->commandData->getOption('paginate');

            if ($paginate) {
                $templateData = str_replace('$RENDER_TYPE$', 'paginate('.$paginate.')', $templateData);
            } else {
                $templateData = str_replace('$RENDER_TYPE$', 'all()', $templateData);
            }
        }

        $templateData = InfyOmHelpers::fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, $this->fileName, $templateData);

        $this->commandData->commandComment("\nController created: ");
        $this->commandData->commandInfo($this->fileName);
    }

    private function generateDataTable()
    {
        $templateData = InfyOmHelpers::get_template('scaffold.datatable', 'laravel-generator');

        $templateData = InfyOmHelpers::fill_template($this->commandData->dynamicVars, $templateData);

        $headerFieldTemplate = InfyOmHelpers::get_template('scaffold.views.datatable_column', $this->templateType);

        $headerFields = [];

        foreach ($this->commandData->fields as $field) {
            if (!$field->inIndex) {
                continue;
            }
            $headerFields[] = $fieldTemplate = InfyOmHelpers::fill_template_with_field_data(
                $this->commandData->dynamicVars,
                $this->commandData->fieldNamesMapping,
                $headerFieldTemplate,
                $field
            );
        }

        $path = $this->commandData->config->pathDataTables;

        $fileName = $this->commandData->modelName.'DataTable.php';

        $fields = implode(','.InfyOmHelpers::infy_nl_tab(1, 3), $headerFields);

        $templateData = str_replace('$DATATABLE_COLUMNS$', $fields, $templateData);

        FileUtil::createFile($path, $fileName, $templateData);

        $this->commandData->commandComment("\nDataTable created: ");
        $this->commandData->commandInfo($fileName);
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->commandData->commandComment('Controller file deleted: '.$this->fileName);
        }

        if ($this->commandData->getAddOn('datatables')) {
            if ($this->rollbackFile($this->commandData->config->pathDataTables, $this->commandData->modelName.'DataTable.php')) {
                $this->commandData->commandComment('DataTable file deleted: '.$this->fileName);
            }
        }
    }
}

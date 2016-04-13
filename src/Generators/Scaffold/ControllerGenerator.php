<?php

namespace InfyOm\Generator\Generators\Scaffold;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Utils\FileUtil;
use InfyOm\Generator\Utils\TemplateUtil;

class ControllerGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    /** @var string */
    private $templateType;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathController;
        $this->templateType = config('infyom.laravel_generator.templates', 'core-templates');
    }

    public function generate()
    {
        if ($this->commandData->getAddOn('datatables')) {
            $templateData = TemplateUtil::getTemplate('scaffold.controller.datatable_controller', 'laravel-generator');

            $this->generateDataTable();
        } else {
            $templateData = TemplateUtil::getTemplate('scaffold.controller.controller', 'laravel-generator');
            
            $paginate = $this->commandData->getOption('paginate');

            if ($paginate) {
                $templateData = str_replace('$RENDER_TYPE$', 'paginate('.$paginate.')', $templateData);
            } else {
                $templateData = str_replace('$RENDER_TYPE$', 'all()', $templateData);
            }
        }

        $templateData = TemplateUtil::fillTemplate($this->commandData->dynamicVars, $templateData);

        $fileName = $this->commandData->modelName.'Controller.php';

        FileUtil::createFile($this->path, $fileName, $templateData);

        $this->commandData->commandComment("\nController created: ");
        $this->commandData->commandInfo($fileName);
    }

    private function generateDataTable()
    {
        $templateData = TemplateUtil::getTemplate('scaffold.datatable', 'laravel-generator');

        $templateData = TemplateUtil::fillTemplate($this->commandData->dynamicVars, $templateData);

        $headerFieldTemplate = TemplateUtil::getTemplate('scaffold.views.datatable_column', $this->templateType);

        $headerFields = [];

        foreach ($this->commandData->inputFields as $field) {
            if (!$field['inIndex']) {
                continue;
            }
            $headerFields[] = $fieldTemplate = TemplateUtil::fillTemplateWithFieldData(
                $this->commandData->dynamicVars,
                $this->commandData->fieldNamesMapping,
                $headerFieldTemplate,
                $field
            );
        }

        $path = $this->commandData->config->pathDataTables;

        $fileName = $this->commandData->modelName.'DataTable.php';

        $fields = implode(",".infy_nl_tab(1, 3), $headerFields);

        $templateData = str_replace('$DATATABLE_COLUMNS$', $fields, $templateData);
        
        FileUtil::createFile($path, $fileName, $templateData);

        $this->commandData->commandComment("\n$fileName created: ");
        $this->commandData->commandInfo($fileName);
    }
}

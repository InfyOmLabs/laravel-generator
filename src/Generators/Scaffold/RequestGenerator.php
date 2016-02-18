<?php

namespace InfyOm\Generator\Generators\Scaffold;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Utils\FileUtil;
use InfyOm\Generator\Utils\TemplateUtil;

class RequestGenerator
{
    /** @var  CommandData */
    private $commandData;

    /** @var string */
    private $path;

    public function __construct($commandData)
    {
        $this->commandData = $commandData;
        $this->path = config('infyom.laravel_generator.path.request', app_path('Http/Requests/'));
    }

    public function generate()
    {
        $this->generateCreateRequest();
        $this->generateUpdateRequest();
    }

    private function generateCreateRequest()
    {
        $templateData = TemplateUtil::getTemplate('scaffold.request.create_request', 'laravel-generator');

        $templateData = TemplateUtil::fillTemplate($this->commandData->dynamicVars, $templateData);

        $fileName = 'Create'.$this->commandData->modelName.'Request.php';

        FileUtil::createFile($this->path, $fileName, $templateData);

        $this->commandData->commandComment("\nCreate Request created: ");
        $this->commandData->commandInfo($fileName);
    }

    private function generateUpdateRequest()
    {
        $templateData = TemplateUtil::getTemplate('scaffold.request.update_request', 'laravel-generator');

        $templateData = TemplateUtil::fillTemplate($this->commandData->dynamicVars, $templateData);

        $fileName = 'Update'.$this->commandData->modelName.'Request.php';

        FileUtil::createFile($this->path, $fileName, $templateData);

        $this->commandData->commandComment("\nUpdate Request created: ");
        $this->commandData->commandInfo($fileName);
    }
}

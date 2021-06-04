<?php

namespace InfyOm\Generator\Generators\Scaffold;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\FileUtil;

class ControllerTestGenerator extends BaseGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    /** @var string */
    private $fileName;

    public function __construct($commandData)
    {
        $this->commandData = $commandData;
        $this->path = config('infyom.laravel_generator.path.controller_tests', base_path('tests/Controllers/'));
        $this->fileName = $this->commandData->modelName.'ControllerTest.php';
    }

    public function generate()
    {
        $templateData = get_template('test.controller_test', 'laravel-generator');

        $templateData = $this->fillTemplate($templateData);

        FileUtil::createFile($this->path, $this->fileName, $templateData);

        $this->commandData->commandObj->comment("\nControllerTest created: ");
        $this->commandData->commandObj->info($this->fileName);
    }

    private function fillTemplate($templateData)
    {
        return fill_template($this->commandData->dynamicVars, $templateData);
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->commandData->commandComment('Controller Test file deleted: '.$this->fileName);
        }
    }
}

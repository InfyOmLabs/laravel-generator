<?php

namespace InfyOm\Generator\Generators;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Utils\FileUtil;
use InfyOm\Generator\Utils\TemplateUtil;

/**
 * Company: Neon Mobile, Copyright 2015, All Rights Reserved.
 * Author: Mitul Golakiya
 * Email: me@mitul.me
 * Date: 29.06.2015
 * Time: 19:25.
 */
class RepositoryTestGenerator
{
    /** @var  CommandData */
    private $commandData;

    /** @var string */
    private $path;

    public function __construct($commandData)
    {
        $this->commandData = $commandData;
        $this->path = config('infyom.laravel_generator.path.repository_test', base_path('tests/'));
    }

    public function generate()
    {
        $templateData = TemplateUtil::getTemplate('test.repository_test', 'laravel-generator');

        $templateData = $this->fillTemplate($templateData);

        $fileName = $this->commandData->modelName.'RepositoryTest.php';

        FileUtil::createFile($this->path, $fileName, $templateData);

        $this->commandData->commandObj->comment("\nRepositoryTest created: ");
        $this->commandData->commandObj->info($fileName);
    }

    private function fillTemplate($templateData)
    {
        $templateData = TemplateUtil::fillTemplate($this->commandData->dynamicVars, $templateData);

        return $templateData;
    }
}

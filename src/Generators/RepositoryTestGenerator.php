<?php

namespace InfyOm\Generator\Generators;

use InfyOm\Generator\Common\GeneratorConfig;
use InfyOm\Generator\Utils\FileUtil;

class RepositoryTestGenerator extends BaseGenerator
{
    private GeneratorConfig $config;

    private string $path;

    private string $fileName;

    public function __construct($config)
    {
        $this->config = $config;
        $this->path = config('laravel_generator.path.repository_test', base_path('tests/Repositories/'));
        $this->fileName = $this->config->modelNames->name.'RepositoryTest.php';
    }

    public function generate()
    {
        $templateData = get_template('test.repository_test', 'laravel-generator');

        $templateData = $this->fillTemplate($templateData);

        FileUtil::createFile($this->path, $this->fileName, $templateData);

        $this->config->commandComment("\nRepositoryTest created: ");
        $this->config->commandInfo($this->fileName);
    }

    private function fillTemplate($templateData)
    {
        $templateData = fill_template($this->config->dynamicVars, $templateData);

        return $templateData;
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->config->commandComment('Repository Test file deleted: '.$this->fileName);
        }
    }
}

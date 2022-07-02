<?php

namespace InfyOm\Generator\Generators;

class RepositoryTestGenerator extends BaseGenerator
{
    private string $fileName;

    public function __construct()
    {
        parent::__construct();

        $this->path = config('laravel_generator.path.repository_test', base_path('tests/Repositories/'));
        $this->fileName = $this->config->modelNames->name.'RepositoryTest.php';
    }

    public function generate()
    {
        $templateData = get_template('test.repository_test', 'laravel-generator');

        $templateData = $this->fillTemplate($templateData);

        g_filesystem()->createFile($this->path, $this->fileName, $templateData);

        $this->config->commandComment(PHP_EOL.'RepositoryTest created: ');
        $this->config->commandInfo($this->fileName);
    }

    private function fillTemplate($templateData): string
    {
        return fill_template($this->config->dynamicVars, $templateData);
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->config->commandComment('Repository Test file deleted: '.$this->fileName);
        }
    }
}

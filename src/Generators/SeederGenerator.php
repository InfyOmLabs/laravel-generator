<?php

namespace InfyOm\Generator\Generators;

class SeederGenerator extends BaseGenerator
{
    private string $fileName;

    public function __construct()
    {
        parent::__construct();

        $this->path = $this->config->paths->seeder;
        $this->fileName = $this->config->modelNames->plural.'TableSeeder.php';
    }

    public function generate()
    {
        $templateData = view('laravel-generator::model.seeder', $this->variables())->render();

        g_filesystem()->createFile($this->path.$this->fileName, $templateData);

        $this->config->commandComment(infy_nl().'Seeder created: ');
        $this->config->commandInfo($this->fileName);
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->config->commandComment('Seeder file deleted: '.$this->fileName);
        }
    }
}

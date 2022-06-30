<?php

namespace InfyOm\Generator\Generators;

use InfyOm\Generator\Common\GeneratorConfig;
use InfyOm\Generator\Utils\FileUtil;

/**
 * Class SeederGenerator.
 */
class SeederGenerator extends BaseGenerator
{
    private GeneratorConfig $config;

    private string $path;
    private string $fileName;

    public function __construct(GeneratorConfig $config)
    {
        $this->config = $config;
        $this->path = $this->config->paths->seeder;
        $this->fileName = $this->config->modelNames->plural.'TableSeeder.php';
    }

    public function generate()
    {
        $templateData = get_template('seeds.model_seeder', 'laravel-generator');

        $templateData = fill_template($this->config->dynamicVars, $templateData);

        FileUtil::createFile($this->path, $this->fileName, $templateData);

        $this->config->commandComment(PHP_EOL.'Seeder created: ');
        $this->config->commandInfo($this->fileName);
    }
}

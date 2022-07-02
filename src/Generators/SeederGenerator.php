<?php /** @noinspection PhpUnusedAliasInspection */

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
        $templateData = get_template('seeds.model_seeder', 'laravel-generator');

        $templateData = fill_template($this->config->dynamicVars, $templateData);

        g_filesystem()->createFile($this->path, $this->fileName, $templateData);

        $this->config->commandComment(PHP_EOL.'Seeder created: ');
        $this->config->commandInfo($this->fileName);
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->config->commandComment('Seeder file deleted: '.$this->fileName);
        }
    }
}

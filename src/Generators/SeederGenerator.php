<?php

namespace InfyOm\Generator\Generators;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Utils\FileUtil;

/**
 * Class SeederGenerator.
 */
class SeederGenerator extends BaseGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;
    private $fileName;

    /**
     * ModelGenerator constructor.
     *
     * @param CommandData $commandData
     */
    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathSeeder;
        $this->fileName = $this->commandData->config->mPlural.'TableSeeder.php';
    }

    public function generate()
    {
        $templateData = get_template('seeds.model_seeder', 'laravel-generator');

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, $this->fileName, $templateData);

        $this->commandData->commandComment("\nSeeder created: ");
        $this->commandData->commandInfo($this->fileName);
    }
}

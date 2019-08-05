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

    public function updateMainSeeder()
    {
        $mainSeederContent = file_get_contents($this->commandData->config->pathDatabaseSeeder);

        $newSeederStatement = '\$this->call('.$this->commandData->config->mPlural.'TableSeeder::class);';

        if (strpos($mainSeederContent, $newSeederStatement) != false) {
            $this->commandData->commandObj->info($this->commandData->config->mPlural.'TableSeeder entry found in DatabaseSeeder. Skipping Adjustment.');

            return;
        }

        $newSeederStatement = infy_tabs(2).$newSeederStatement.infy_nl();

        preg_match_all('/\\$this->call\\((.*);/', $mainSeederContent, $matches);

        $totalMatches = count($matches[0]);
        $lastSeederStatement = $matches[0][$totalMatches - 1];

        $replacePosition = strpos($mainSeederContent, $lastSeederStatement);

        $mainSeederContent = substr_replace($mainSeederContent, $newSeederStatement, $replacePosition + strlen($lastSeederStatement) + 1, 0);

        file_put_contents($this->commandData->config->pathDatabaseSeeder, $mainSeederContent);
        $this->commandData->commandComment('Main Seeder file updated.');
    }
}

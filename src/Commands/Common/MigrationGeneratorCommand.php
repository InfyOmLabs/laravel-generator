<?php

namespace InfyOm\Generator\Commands\Common;

use InfyOm\Generator\Commands\BaseCommand;
use InfyOm\Generator\Common\ClassInjectionConfig;
use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\MigrationGenerator;

class MigrationGeneratorCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'infyom:migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create migration command';

    /**
     * Create a new command instance.
     * @throws \ReflectionException
     */
    public function __construct()
    {
        parent::__construct();

        $this->commandData = ClassInjectionConfig::createClassByConfigPath('Common.command_data', [$this, CommandData::$COMMAND_TYPE_API]);
    }

    /**
     * Execute the command.
     *
     * @return void
     * @throws \ReflectionException
     */
    public function handle()
    {
        parent::handle();

        if ($this->commandData->getOption('fromTable')) {
            $this->error('fromTable option is not allowed to use with migration generator');

            return;
        }

        /** @var MigrationGenerator $migrationGenerator */
        $migrationGenerator = ClassInjectionConfig::createClassByConfigPath('Generators.migration', [$this->commandData]);
        $migrationGenerator->generate();

        $this->performPostActionsWithMigration();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return array_merge(parent::getOptions(), []);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(), []);
    }
}

<?php

namespace InfyOm\Generator\Commands;

use InfyOm\Generator\Common\ClassInjectionConfig;
use InfyOm\Generator\Common\CommandData;

class APIScaffoldGeneratorCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'infyom:api_scaffold';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a full CRUD API and Scaffold for given model';

    /**
     * Create a new command instance.
     * @throws \ReflectionException
     */
    public function __construct()
    {
        parent::__construct();
        $this->commandData = ClassInjectionConfig::createClassByConfigPath('Common.command_data', [$this, CommandData::$COMMAND_TYPE_API_SCAFFOLD]);
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        parent::handle();

        $this->generateCommonItems();

        $this->generateAPIItems();

        $this->generateScaffoldItems();

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

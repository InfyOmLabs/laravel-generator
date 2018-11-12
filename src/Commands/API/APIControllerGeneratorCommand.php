<?php

namespace InfyOm\Generator\Commands\API;

use InfyOm\Generator\Commands\BaseCommand;
use InfyOm\Generator\Common\ClassInjectionConfig;
use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\API\APIControllerGenerator;

class APIControllerGeneratorCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'infyom.api:controller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an api controller command';

    /**
     * Create a new command instance.
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

        /** @var APIControllerGenerator $controllerGenerator */
        $controllerGenerator = ClassInjectionConfig::createClassByConfigPath('Generators.API.api_controller', [$this->commandData]);
        $controllerGenerator->generate();

        $this->performPostActions();
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

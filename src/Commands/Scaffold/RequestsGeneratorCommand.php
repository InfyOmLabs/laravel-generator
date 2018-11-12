<?php

namespace InfyOm\Generator\Commands\Scaffold;

use InfyOm\Generator\Commands\BaseCommand;
use InfyOm\Generator\Common\ClassInjectionConfig;
use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\Scaffold\RequestGenerator;

class RequestsGeneratorCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'infyom.scaffold:requests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a full CRUD views for given model';

    /**
     * Create a new command instance.
     * @throws \ReflectionException
     */
    public function __construct()
    {
        parent::__construct();

        $this->commandData = ClassInjectionConfig::createClassByConfigPath('Common.command_data', [$this, CommandData::$COMMAND_TYPE_SCAFFOLD]);
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

        /** @var RequestGenerator $requestGenerator */
        $requestGenerator = ClassInjectionConfig::createClassByConfigPath('Generators.Scaffold.request', [$this->commandData]);
        $requestGenerator->generate();

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

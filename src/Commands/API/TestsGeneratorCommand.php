<?php

namespace InfyOm\Generator\Commands\API;

use InfyOm\Generator\Commands\BaseCommand;
use InfyOm\Generator\Common\ClassInjectionConfig;
use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\API\APITestGenerator;
use InfyOm\Generator\Generators\RepositoryTestGenerator;
use InfyOm\Generator\Generators\TestTraitGenerator;

class TestsGeneratorCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'infyom.api:tests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create tests command';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->commandData = new CommandData($this, CommandData::$COMMAND_TYPE_API);
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

        /** @var RepositoryTestGenerator $repositoryGenerator */
        $repositoryTestGenerator = ClassInjectionConfig::createClassByConfigPath('Generators.repository_test', [$this->commandData]);
        $repositoryTestGenerator->generate();

        /** @var TestTraitGenerator $testTraitGenerator */
        $testTraitGenerator = ClassInjectionConfig::createClassByConfigPath('Generators.test_trait', [$this->commandData]);
        $testTraitGenerator->generate();

        /** @var APITestGenerator $apiTestGenerator */
        $apiTestGenerator = ClassInjectionConfig::createClassByConfigPath('Generators.API.api_test', [$this->commandData]);
        $apiTestGenerator->generate();

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

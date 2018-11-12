<?php

namespace InfyOm\Generator\Commands\VueJs;

use InfyOm\Generator\Commands\BaseCommand;
use InfyOm\Generator\Common\ClassInjectionConfig;
use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\MigrationGenerator;
use InfyOm\Generator\Generators\ModelGenerator;
use InfyOm\Generator\Generators\RepositoryGenerator;
use InfyOm\Generator\Generators\Scaffold\MenuGenerator;
use InfyOm\Generator\Generators\VueJs\APIRequestGenerator;
use InfyOm\Generator\Generators\VueJs\ControllerGenerator;
use InfyOm\Generator\Generators\VueJs\ModelJsConfigGenerator;
use InfyOm\Generator\Generators\VueJs\RoutesGenerator;
use InfyOm\Generator\Generators\VueJs\ViewGenerator;

class VueJsGeneratorCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'infyom:vuejs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a full CRUD views and config using VueJs Framework for given model';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->commandData = new CommandData($this, CommandData::$COMMAND_TYPE_VUEJS);
    }

    /**
     * Execute the command.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        parent::handle();

        if (!$this->commandData->getOption('fromTable')) {
            /** @var MigrationGenerator $migrationGenerator */
            $migrationGenerator = ClassInjectionConfig::createClassByConfigPath('Generators.migration', [$this->commandData]);
            $migrationGenerator->generate();
        }

        /** @var ModelGenerator $modelGenerator */
        $modelGenerator = ClassInjectionConfig::createClassByConfigPath('Generators.model', [$this->commandData]);
        $modelGenerator->generate();

        /** @var RepositoryGenerator $repositoryGenerator */
        $repositoryGenerator = ClassInjectionConfig::createClassByConfigPath('Generators.repository', [$this->commandData]);
        $repositoryGenerator->generate();

        /** @var APIRequestGenerator $requestGenerator */
        $requestGenerator = ClassInjectionConfig::createClassByConfigPath('Generators.API.api_request', [$this->commandData]);
        $requestGenerator->generate();

        /** @var ControllerGenerator $controllerGenerator */
        $controllerGenerator = ClassInjectionConfig::createClassByConfigPath('Generators.Scaffold.controller', [$this->commandData]);
        $controllerGenerator->generate();

        /** @var ViewGenerator $viewGenerator */
        $viewGenerator = ClassInjectionConfig::createClassByConfigPath('Generators.Scaffold.view', [$this->commandData]);
        $viewGenerator->generate();

        $modelJsConfigGenerator = new ModelJsConfigGenerator($this->commandData);
        $modelJsConfigGenerator->generate();

        /** @var RoutesGenerator $routeGenerator */
        $routeGenerator = ClassInjectionConfig::createClassByConfigPath('Generators.Scaffold.routes', [$this->commandData]);
        $routeGenerator->generate();

        if ($this->commandData->config->getAddOn('menu.enabled')) {
            /** @var MenuGenerator $menuGenerator */
            $menuGenerator = ClassInjectionConfig::createClassByConfigPath('Generators.Scaffold.menu', [$this->commandData]);
            $menuGenerator->generate();
        }

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

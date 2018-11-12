<?php

namespace InfyOm\Generator\Commands;

use Illuminate\Console\Command;
use InfyOm\Generator\Common\ClassInjectionConfig;
use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\API\APIControllerGenerator;
use InfyOm\Generator\Generators\API\APIRequestGenerator;
use InfyOm\Generator\Generators\API\APIRoutesGenerator;
use InfyOm\Generator\Generators\API\APITestGenerator;
use InfyOm\Generator\Generators\MigrationGenerator;
use InfyOm\Generator\Generators\ModelGenerator;
use InfyOm\Generator\Generators\RepositoryGenerator;
use InfyOm\Generator\Generators\RepositoryTestGenerator;
use InfyOm\Generator\Generators\Scaffold\ControllerGenerator;
use InfyOm\Generator\Generators\Scaffold\MenuGenerator;
use InfyOm\Generator\Generators\Scaffold\RequestGenerator;
use InfyOm\Generator\Generators\Scaffold\RoutesGenerator;
use InfyOm\Generator\Generators\Scaffold\ViewGenerator;
use InfyOm\Generator\Generators\TestTraitGenerator;
use InfyOm\Generator\Generators\VueJs\ControllerGenerator as VueJsControllerGenerator;
use InfyOm\Generator\Generators\VueJs\ModelJsConfigGenerator;
use InfyOm\Generator\Generators\VueJs\RoutesGenerator as VueJsRoutesGenerator;
use InfyOm\Generator\Generators\VueJs\ViewGenerator as VueJsViewGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class RollbackGeneratorCommand extends Command
{
    /**
     * The command Data.
     *
     * @var CommandData
     */
    public $commandData;
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'infyom:rollback';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback a full CRUD API and Scaffold for given model';

    /**
     * @var Composer
     */
    public $composer;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->composer = app()['composer'];
    }

    /**
     * Execute the command.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        if (!in_array($this->argument('type'), [
            CommandData::$COMMAND_TYPE_API,
            CommandData::$COMMAND_TYPE_SCAFFOLD,
            CommandData::$COMMAND_TYPE_API_SCAFFOLD,
            CommandData::$COMMAND_TYPE_VUEJS,
        ])) {
            $this->error('invalid rollback type');
        }

        $this->commandData = new CommandData($this, $this->argument('type'));
        $this->commandData->config->mName = $this->commandData->modelName = $this->argument('model');

        $this->commandData->config->init($this->commandData, ['tableName', 'prefix']);

        /** @var MigrationGenerator $migrationGenerator */
        $migrationGenerator = ClassInjectionConfig::createClassByConfigPath('Generators.migration', [$this->commandData]);
        $migrationGenerator->rollback();

        /** @var ModelGenerator $modelGenerator */
        $modelGenerator = ClassInjectionConfig::createClassByConfigPath('Generators.model', [$this->commandData]);
        $modelGenerator->rollback();

        /** @var RepositoryGenerator $repositoryGenerator */
        $repositoryGenerator = ClassInjectionConfig::createClassByConfigPath('Generators.repository', [$this->commandData]);
        $repositoryGenerator->rollback();

        /** @var APIRequestGenerator $requestGenerator */
        $requestGenerator = ClassInjectionConfig::createClassByConfigPath('Generators.API.api_request', [$this->commandData]);
        $requestGenerator->rollback();

        /** @var APIControllerGenerator $controllerGenerator */
        $controllerGenerator = ClassInjectionConfig::createClassByConfigPath('Generators.API.api_controller', [$this->commandData]);
        $controllerGenerator->rollback();

        /** @var APIRoutesGenerator $routesGenerator */
        $routesGenerator = ClassInjectionConfig::createClassByConfigPath('Generators.API.api_routes', [$this->commandData]);
        $routesGenerator->rollback();

        /** @var RequestGenerator $requestGenerator */
        $requestGenerator = ClassInjectionConfig::createClassByConfigPath('Generators.Scaffold.request', [$this->commandData]);
        $requestGenerator->rollback();

        /** @var ControllerGenerator $controllerGenerator */
        $controllerGenerator = ClassInjectionConfig::createClassByConfigPath('Generators.Scaffold.controller', [$this->commandData]);
        $controllerGenerator->rollback();

        /** @var ViewGenerator $viewGenerator */
        $viewGenerator = ClassInjectionConfig::createClassByConfigPath('Generators.Scaffold.view', [$this->commandData]);
        $viewGenerator->rollback();

        /** @var RoutesGenerator $routeGenerator */
        $routeGenerator = ClassInjectionConfig::createClassByConfigPath('Generators.Scaffold.routes', [$this->commandData]);
        $routeGenerator->rollback();

        /** @var VueJsControllerGenerator $controllerGenerator */
        $controllerGenerator = ClassInjectionConfig::createClassByConfigPath('Generators.VueJs.controller', [$this->commandData]);
        $controllerGenerator->rollback();

        /** @var VueJsRoutesGenerator $routesGenerator */
        $routesGenerator = ClassInjectionConfig::createClassByConfigPath('Generators.VueJs.routes', [$this->commandData]);
        $routesGenerator->rollback();

        /** @var VueJsViewGenerator $routesGenerator */
        $viewGenerator = ClassInjectionConfig::createClassByConfigPath('Generators.VueJs.view', [$this->commandData]);
        $viewGenerator->rollback();

        /** @var ModelJsConfigGenerator $modelJsConfigGenerator */
        $modelJsConfigGenerator = ClassInjectionConfig::createClassByConfigPath('Generators.VueJs.model_js_config', [$this->commandData]);
        $modelJsConfigGenerator->rollback();

        if ($this->commandData->getAddOn('tests')) {
            /** @var RepositoryTestGenerator $repositoryGenerator */
            $repositoryTestGenerator = ClassInjectionConfig::createClassByConfigPath('Generators.repository_test', [$this->commandData]);
            $repositoryTestGenerator->rollback();

            /** @var TestTraitGenerator $testTraitGenerator */
            $testTraitGenerator = ClassInjectionConfig::createClassByConfigPath('Generators.test_trait', [$this->commandData]);
            $testTraitGenerator->rollback();

            /** @var APITestGenerator $apiTestGenerator */
            $apiTestGenerator = ClassInjectionConfig::createClassByConfigPath('Generators.API.api_test', [$this->commandData]);
            $apiTestGenerator->rollback();
        }

        if ($this->commandData->config->getAddOn('menu.enabled')) {
            /** @var MenuGenerator $menuGenerator */
            $menuGenerator = ClassInjectionConfig::createClassByConfigPath('Generators.Scaffold.menu', [$this->commandData]);
            $menuGenerator->rollback();
        }

        $this->info('Generating autoload files');
        $this->composer->dumpOptimized();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return [
            ['tableName', null, InputOption::VALUE_REQUIRED, 'Table Name'],
            ['prefix', null, InputOption::VALUE_REQUIRED, 'Prefix for all files'],
        ];
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['model', InputArgument::REQUIRED, 'Singular Model name'],
            ['type', InputArgument::REQUIRED, 'Rollback type: (api / scaffold / scaffold_api)'],
        ];
    }
}

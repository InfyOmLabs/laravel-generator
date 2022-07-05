<?php

namespace InfyOm\Generator\Commands;

use InfyOm\Generator\Common\GeneratorConfig;
use InfyOm\Generator\Generators\API\APIControllerGenerator;
use InfyOm\Generator\Generators\API\APIRequestGenerator;
use InfyOm\Generator\Generators\API\APIRoutesGenerator;
use InfyOm\Generator\Generators\API\APITestGenerator;
use InfyOm\Generator\Generators\FactoryGenerator;
use InfyOm\Generator\Generators\MigrationGenerator;
use InfyOm\Generator\Generators\ModelGenerator;
use InfyOm\Generator\Generators\RepositoryGenerator;
use InfyOm\Generator\Generators\RepositoryTestGenerator;
use InfyOm\Generator\Generators\Scaffold\ControllerGenerator;
use InfyOm\Generator\Generators\Scaffold\MenuGenerator;
use InfyOm\Generator\Generators\Scaffold\RequestGenerator;
use InfyOm\Generator\Generators\Scaffold\RoutesGenerator;
use InfyOm\Generator\Generators\Scaffold\ViewGenerator;
use InfyOm\Generator\Generators\SeederGenerator;
use Symfony\Component\Console\Input\InputArgument;

class RollbackGeneratorCommand extends BaseCommand
{
    public GeneratorConfig $config;

    protected $name = 'infyom:rollback';

    protected $description = 'Rollback a full CRUD API and Scaffold for given model';

    public function handle()
    {
        $this->config = app(GeneratorConfig::class);
        $this->config->setCommand($this);
        $this->config->init();

        $type = $this->argument('type');
        if (!in_array($type, ['api', 'scaffold', 'api_scaffold'])) {
            $this->error('Invalid rollback type');

            return 1;
        }

        $this->fireFileDeletingEvent($type);
        $views = $this->option('views');
        if (!empty($views)) {
            $views = explode(',', $views);
            $viewGenerator = new ViewGenerator();
            $viewGenerator->rollback($views);

            $this->info('Generating autoload files');
            $this->composer->dumpOptimized();
            $this->fireFileDeletedEvent($type);

            return 0;
        }

        $migrationGenerator = app(MigrationGenerator::class);
        $migrationGenerator->rollback();

        $modelGenerator = app(ModelGenerator::class);
        $modelGenerator->rollback();

        if ($this->config->options->repositoryPattern) {
            $repositoryGenerator = app(RepositoryGenerator::class);
            $repositoryGenerator->rollback();
        }

        if (in_array($type, ['api', 'api_scaffold'])) {
            $requestGenerator = app(APIRequestGenerator::class);
            $requestGenerator->rollback();

            $controllerGenerator = app(APIControllerGenerator::class);
            $controllerGenerator->rollback();

            $routesGenerator = app(APIRoutesGenerator::class);
            $routesGenerator->rollback();
        }

        if (in_array($type, ['scaffold', 'api_scaffold'])) {
            $requestGenerator = app(RequestGenerator::class);
            $requestGenerator->rollback();

            $controllerGenerator = app(ControllerGenerator::class);
            $controllerGenerator->rollback();

            $viewGenerator = app(ViewGenerator::class);
            $viewGenerator->rollback();

            $routeGenerator = app(RoutesGenerator::class);
            $routeGenerator->rollback();

            $menuGenerator = app(MenuGenerator::class);
            $menuGenerator->rollback();
        }

        if ($this->config->options->tests) {
            $repositoryTestGenerator = app(RepositoryTestGenerator::class);
            $repositoryTestGenerator->rollback();

            $apiTestGenerator = app(APITestGenerator::class);
            $apiTestGenerator->rollback();
        }

        if ($this->config->options->factory or $this->config->options->tests) {
            $factoryGenerator = app(FactoryGenerator::class);
            $factoryGenerator->rollback();
        }

        if ($this->config->options->seeder) {
            $seederGenerator = app(SeederGenerator::class);
            $seederGenerator->rollback();
        }

        $this->info('Generating autoload files');
        $this->composer->dumpOptimized();

        $this->fireFileDeletedEvent($type);

        return 0;
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
            ['type', InputArgument::REQUIRED, 'Rollback type: (api / scaffold / api_scaffold)'],
        ];
    }
}

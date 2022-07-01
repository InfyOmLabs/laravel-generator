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
use InfyOm\Generator\Utils\FileUtil;
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

        $this->fireEvent($type, FileUtil::FILE_DELETING);
        $views = $this->option('views');
        if (!empty($views)) {
            $views = explode(',', $views);
            $viewGenerator = new ViewGenerator();
            $viewGenerator->rollback($views);

            $this->info('Generating autoload files');
            $this->composer->dumpOptimized();
            $this->fireEvent($type, FileUtil::FILE_DELETED);

            return 0;
        }

        $migrationGenerator = new MigrationGenerator();
        $migrationGenerator->rollback();

        $modelGenerator = new ModelGenerator();
        $modelGenerator->rollback();

        if ($this->config->options->repositoryPattern) {
            $repositoryGenerator = new RepositoryGenerator();
            $repositoryGenerator->rollback();
        }

        if (in_array($type, ['api', 'api_scaffold'])) {
            $requestGenerator = new APIRequestGenerator();
            $requestGenerator->rollback();

            $controllerGenerator = new APIControllerGenerator();
            $controllerGenerator->rollback();

            $routesGenerator = new APIRoutesGenerator();
            $routesGenerator->rollback();
        }

        if (in_array($type, ['scaffold', 'api_scaffold'])) {
            $requestGenerator = new RequestGenerator();
            $requestGenerator->rollback();

            $controllerGenerator = new ControllerGenerator();
            $controllerGenerator->rollback();

            $viewGenerator = new ViewGenerator();
            $viewGenerator->rollback();

            $routeGenerator = new RoutesGenerator();
            $routeGenerator->rollback();

            $menuGenerator = new MenuGenerator();
            $menuGenerator->rollback();
        }

        if ($this->config->addons->tests) {
            $repositoryTestGenerator = new RepositoryTestGenerator();
            $repositoryTestGenerator->rollback();

            $apiTestGenerator = new APITestGenerator();
            $apiTestGenerator->rollback();
        }

        if ($this->config->options->factory) {
            $factoryGenerator = new FactoryGenerator();
            $factoryGenerator->rollback();
        }

        if ($this->config->options->seeder) {
            $seederGenerator = new SeederGenerator();
            $seederGenerator->rollback();
        }

        $this->info('Generating autoload files');
        $this->composer->dumpOptimized();

        $this->fireEvent($type, FileUtil::FILE_DELETED);

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

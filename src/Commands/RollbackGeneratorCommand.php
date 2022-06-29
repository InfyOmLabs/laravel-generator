<?php

namespace InfyOm\Generator\Commands;

use Illuminate\Console\Command;
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
use InfyOm\Generator\Utils\FileUtil;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class RollbackGeneratorCommand extends Command
{
    public GeneratorConfig $config;

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
     * @return int
     */
    public function handle()
    {
        $type = $this->argument('type');
        if (!in_array($type, ['api', 'scaffold', 'api_scaffold'])) {
            $this->error('invalid rollback type');

            return 1;
        }

        $this->config = new GeneratorConfig($this);
        $this->config->init();

        $this->config->command->fireEvent($type, FileUtil::FILE_DELETING);
        $views = $this->option('views');
        if (!empty($views)) {
            $views = explode(',', $views);
            $viewGenerator = new ViewGenerator($this->config);
            $viewGenerator->rollback($views);

            $this->info('Generating autoload files');
            $this->composer->dumpOptimized();
            $this->config->fireEvent($type, FileUtil::FILE_DELETED);

            return 0;
        }

        $migrationGenerator = new MigrationGenerator($this->config);
        $migrationGenerator->rollback();

        $modelGenerator = new ModelGenerator($this->config);
        $modelGenerator->rollback();

        $repositoryGenerator = new RepositoryGenerator($this->config);
        $repositoryGenerator->rollback();

        $requestGenerator = new APIRequestGenerator($this->config);
        $requestGenerator->rollback();

        $controllerGenerator = new APIControllerGenerator($this->config);
        $controllerGenerator->rollback();

        $routesGenerator = new APIRoutesGenerator($this->config);
        $routesGenerator->rollback();

        $requestGenerator = new RequestGenerator($this->config);
        $requestGenerator->rollback();

        $controllerGenerator = new ControllerGenerator($this->config);
        $controllerGenerator->rollback();

        $viewGenerator = new ViewGenerator($this->config);
        $viewGenerator->rollback();

        $routeGenerator = new RoutesGenerator($this->config);
        $routeGenerator->rollback();

        if ($this->config->getAddOn('tests')) {
            $repositoryTestGenerator = new RepositoryTestGenerator($this->config);
            $repositoryTestGenerator->rollback();

            $apiTestGenerator = new APITestGenerator($this->config);
            $apiTestGenerator->rollback();
        }

        $factoryGenerator = new FactoryGenerator($this->config);
        $factoryGenerator->rollback();

        if ($this->config->config->getAddOn('menu.enabled')) {
            $menuGenerator = new MenuGenerator($this->config);
            $menuGenerator->rollback();
        }

        $this->info('Generating autoload files');
        $this->composer->dumpOptimized();

        $this->config->fireEvent($type, FileUtil::FILE_DELETED);

        return 0;
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
            ['plural', null, InputOption::VALUE_REQUIRED, 'Plural Model name'],
            ['views', null, InputOption::VALUE_REQUIRED, 'Views to rollback'],
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
            ['type', InputArgument::REQUIRED, 'Rollback type: (api / scaffold / api_scaffold)'],
        ];
    }
}

<?php

namespace InfyOm\Generator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\API\APIControllerGenerator;
use InfyOm\Generator\Generators\API\APIRequestGenerator;
use InfyOm\Generator\Generators\API\APIResourceGenerator;
use InfyOm\Generator\Generators\API\APIRoutesGenerator;
use InfyOm\Generator\Generators\API\APITestGenerator;
use InfyOm\Generator\Generators\FactoryGenerator;
use InfyOm\Generator\Generators\MigrationGenerator;
use InfyOm\Generator\Generators\ModelGenerator;
use InfyOm\Generator\Generators\RepositoryGenerator;
use InfyOm\Generator\Generators\RepositoryTestGenerator;
use InfyOm\Generator\Generators\Scaffold\ControllerGenerator;
use InfyOm\Generator\Generators\Scaffold\JQueryDatatableAssetsGenerator;
use InfyOm\Generator\Generators\Scaffold\MenuGenerator;
use InfyOm\Generator\Generators\Scaffold\RequestGenerator;
use InfyOm\Generator\Generators\Scaffold\RoutesGenerator;
use InfyOm\Generator\Generators\Scaffold\ViewGenerator;
use InfyOm\Generator\Generators\SeederGenerator;
use InfyOm\Generator\Utils\FileUtil;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class BaseCommand extends Command
{
    /**
     * The command Data.
     *
     * @var CommandData
     */
    public $commandData;

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

    public function handle()
    {
        $this->commandData->modelName = $this->argument('model');

        $this->commandData->initCommandData();
        $this->commandData->getFields();
    }

    public function generateCommonItems()
    {
        if (!$this->commandData->getOption('fromTable') and !$this->isSkip('migration')) {
            $migrationGenerator = new MigrationGenerator($this->commandData);
            $migrationGenerator->generate();
        }

        if (!$this->isSkip('model')) {
            $modelGenerator = new ModelGenerator($this->commandData);
            $modelGenerator->generate();
        }

        if (!$this->isSkip('repository') && $this->commandData->getOption('repositoryPattern')) {
            $repositoryGenerator = new RepositoryGenerator($this->commandData);
            $repositoryGenerator->generate();
        }

        if ($this->commandData->getOption('factory') || (
            !$this->isSkip('tests') and $this->commandData->getAddOn('tests')
        )) {
            $factoryGenerator = new FactoryGenerator($this->commandData);
            $factoryGenerator->generate();
        }

        if ($this->commandData->getOption('seeder')) {
            $seederGenerator = new SeederGenerator($this->commandData);
            $seederGenerator->generate();
        }
    }

    public function generateAPIItems()
    {
        if (!$this->isSkip('requests') and !$this->isSkip('api_requests')) {
            $requestGenerator = new APIRequestGenerator($this->commandData);
            $requestGenerator->generate();
        }

        if (!$this->isSkip('controllers') and !$this->isSkip('api_controller')) {
            $controllerGenerator = new APIControllerGenerator($this->commandData);
            $controllerGenerator->generate();
        }

        if (!$this->isSkip('routes') and !$this->isSkip('api_routes')) {
            $routesGenerator = new APIRoutesGenerator($this->commandData);
            $routesGenerator->generate();
        }

        if (!$this->isSkip('tests') and $this->commandData->getAddOn('tests')) {
            if ($this->commandData->getOption('repositoryPattern')) {
                $repositoryTestGenerator = new RepositoryTestGenerator($this->commandData);
                $repositoryTestGenerator->generate();
            }

            $apiTestGenerator = new APITestGenerator($this->commandData);
            $apiTestGenerator->generate();
        }
        if ($this->commandData->getOption('resources')) {
            $apiResourceGenerator = new APIResourceGenerator($this->commandData);
            $apiResourceGenerator->generate();
        }
    }

    public function generateScaffoldItems()
    {
        if (!$this->isSkip('requests') and !$this->isSkip('scaffold_requests')) {
            $requestGenerator = new RequestGenerator($this->commandData);
            $requestGenerator->generate();
        }

        if (!$this->isSkip('controllers') and !$this->isSkip('scaffold_controller')) {
            $controllerGenerator = new ControllerGenerator($this->commandData);
            $controllerGenerator->generate();
        }

        if (!$this->isSkip('views')) {
            $viewGenerator = new ViewGenerator($this->commandData);
            $viewGenerator->generate();
        }

        if (!$this->isSkip('routes') and !$this->isSkip('scaffold_routes')) {
            $routeGenerator = new RoutesGenerator($this->commandData);
            $routeGenerator->generate();
        }

        if (!$this->isSkip('menu') and $this->commandData->config->getAddOn('menu.enabled')) {
            $menuGenerator = new MenuGenerator($this->commandData);
            $menuGenerator->generate();
        }

        if ($this->commandData->jqueryDT()) {
            $assetsGenerator = new JQueryDatatableAssetsGenerator($this->commandData);
            $assetsGenerator->generate();
        }
    }

    public function performPostActions($runMigration = false)
    {
        if ($this->commandData->getOption('save')) {
            $this->saveSchemaFile();
        }

        if ($runMigration) {
            if ($this->commandData->getOption('forceMigrate')) {
                $this->runMigration();
            } elseif (!$this->commandData->getOption('fromTable') and !$this->isSkip('migration')) {
                $requestFromConsole = (php_sapi_name() == 'cli') ? true : false;
                if ($this->commandData->getOption('jsonFromGUI') && $requestFromConsole) {
                    $this->runMigration();
                } elseif ($requestFromConsole && $this->confirm("\nDo you want to migrate database? [y|N]", false)) {
                    $this->runMigration();
                }
            }
        }

        if ($this->commandData->getOption('localized')) {
            $this->saveLocaleFile();
        }

        if (!$this->isSkip('dump-autoload')) {
            $this->info('Generating autoload files');
            $this->composer->dumpOptimized();
        }
    }

    public function runMigration()
    {
        $migrationPath = config('infyom.laravel_generator.path.migration', database_path('migrations/'));
        $path = Str::after($migrationPath, base_path()); // get path after base_path
        $this->call('migrate', ['--path' => $path, '--force' => true]);

        return true;
    }

    public function isSkip($skip)
    {
        if ($this->commandData->getOption('skip')) {
            return in_array($skip, (array) $this->commandData->getOption('skip'));
        }

        return false;
    }

    public function performPostActionsWithMigration()
    {
        $this->performPostActions(true);
    }

    private function saveSchemaFile()
    {
        $fileFields = [];

        foreach ($this->commandData->fields as $field) {
            $fileFields[] = [
                'name'        => $field->name,
                'dbType'      => $field->dbInput,
                'htmlType'    => $field->htmlInput,
                'validations' => $field->validations,
                'searchable'  => $field->isSearchable,
                'fillable'    => $field->isFillable,
                'primary'     => $field->isPrimary,
                'inForm'      => $field->inForm,
                'inIndex'     => $field->inIndex,
                'inView'      => $field->inView,
            ];
        }

        foreach ($this->commandData->relations as $relation) {
            $fileFields[] = [
                'type'     => 'relation',
                'relation' => $relation->type.','.implode(',', $relation->inputs),
            ];
        }

        $path = config('infyom.laravel_generator.path.schema_files', resource_path('model_schemas/'));

        $fileName = $this->commandData->modelName.'.json';

        if (file_exists($path.$fileName) && !$this->confirmOverwrite($fileName)) {
            return;
        }
        FileUtil::createFile($path, $fileName, json_encode($fileFields, JSON_PRETTY_PRINT));
        $this->commandData->commandComment("\nSchema File saved: ");
        $this->commandData->commandInfo($fileName);
    }

    private function saveLocaleFile()
    {
        $locales = [
            'singular' => $this->commandData->modelName,
            'plural'   => $this->commandData->config->mPlural,
            'fields'   => [],
        ];

        foreach ($this->commandData->fields as $field) {
            $locales['fields'][$field->name] = Str::title(str_replace('_', ' ', $field->name));
        }

        $path = config('infyom.laravel_generator.path.models_locale_files', base_path('resources/lang/en/models/'));

        $fileName = $this->commandData->config->mCamelPlural.'.php';

        if (file_exists($path.$fileName) && !$this->confirmOverwrite($fileName)) {
            return;
        }
        $content = "<?php\n\nreturn ".var_export($locales, true).';'.\PHP_EOL;
        FileUtil::createFile($path, $fileName, $content);
        $this->commandData->commandComment("\nModel Locale File saved: ");
        $this->commandData->commandInfo($fileName);
    }

    /**
     * @param $fileName
     * @param string $prompt
     *
     * @return bool
     */
    protected function confirmOverwrite($fileName, $prompt = '')
    {
        $prompt = (empty($prompt))
            ? $fileName.' already exists. Do you want to overwrite it? [y|N]'
            : $prompt;

        return $this->confirm($prompt, false);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return [
            ['fieldsFile', null, InputOption::VALUE_REQUIRED, 'Fields input as json file'],
            ['jsonFromGUI', null, InputOption::VALUE_REQUIRED, 'Direct Json string while using GUI interface'],
            ['plural', null, InputOption::VALUE_REQUIRED, 'Plural Model name'],
            ['tableName', null, InputOption::VALUE_REQUIRED, 'Table Name'],
            ['fromTable', null, InputOption::VALUE_NONE, 'Generate from existing table'],
            ['ignoreFields', null, InputOption::VALUE_REQUIRED, 'Ignore fields while generating from table'],
            ['save', null, InputOption::VALUE_NONE, 'Save model schema to file'],
            ['primary', null, InputOption::VALUE_REQUIRED, 'Custom primary key'],
            ['prefix', null, InputOption::VALUE_REQUIRED, 'Prefix for all files'],
            ['paginate', null, InputOption::VALUE_REQUIRED, 'Pagination for index.blade.php'],
            ['skip', null, InputOption::VALUE_REQUIRED, 'Skip Specific Items to Generate (migration,model,controllers,api_controller,scaffold_controller,repository,requests,api_requests,scaffold_requests,routes,api_routes,scaffold_routes,views,tests,menu,dump-autoload)'],
            ['datatables', null, InputOption::VALUE_REQUIRED, 'Override datatables settings'],
            ['views', null, InputOption::VALUE_REQUIRED, 'Specify only the views you want generated: index,create,edit,show'],
            ['relations', null, InputOption::VALUE_NONE, 'Specify if you want to pass relationships for fields'],
            ['softDelete', null, InputOption::VALUE_NONE, 'Soft Delete Option'],
            ['forceMigrate', null, InputOption::VALUE_NONE, 'Specify if you want to run migration or not'],
            ['factory', null, InputOption::VALUE_NONE, 'To generate factory'],
            ['seeder', null, InputOption::VALUE_NONE, 'To generate seeder'],
            ['localized', null, InputOption::VALUE_NONE, 'Localize files.'],
            ['repositoryPattern', null, InputOption::VALUE_REQUIRED, 'Repository Pattern'],
            ['resources', null, InputOption::VALUE_REQUIRED, 'Resources'],
            ['connection', null, InputOption::VALUE_REQUIRED, 'Specify connection name'],
            ['jqueryDT', null, InputOption::VALUE_NONE, 'Generate listing screen into JQuery Datatables'],
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
        ];
    }
}

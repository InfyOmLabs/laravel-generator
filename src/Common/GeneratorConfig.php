<?php

namespace InfyOm\Generator\Common;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use InfyOm\Generator\DTOs\GeneratorAddons;
use InfyOm\Generator\DTOs\GeneratorNamespaces;
use InfyOm\Generator\DTOs\GeneratorOptions;
use InfyOm\Generator\DTOs\GeneratorPaths;
use InfyOm\Generator\DTOs\GeneratorPrefixes;
use InfyOm\Generator\DTOs\ModelNames;

class GeneratorConfig
{
    public GeneratorNamespaces $namespaces;
    public GeneratorPaths $paths;
    public ModelNames $modelNames;
    public GeneratorPrefixes $prefixes;
    public GeneratorAddons $addons;
    public GeneratorOptions $options;
    public Command $command;

    /** @var GeneratorField[] */
    public array $fields = [];

    /** @var GeneratorFieldRelation[] */
    public array $relations = [];

    public array $dynamicVars = [];

    public string $tableName;
    public string $tableType;
    public string $primaryName;
    public string $connection;

    public function init()
    {
        $this->loadModelNames();
        $this->loadPrefixes();
        $this->loadPaths();
        $this->tableType = config('laravel_generator.tables', 'blade');
        $this->loadNamespaces();
        $this->prepareTableName();
        $this->preparePrimaryName();
        $this->prepareAddons();
        $this->prepareOptions();
        $this->loadDynamicVariables();
    }

    public function setCommand(Command &$command)
    {
        $this->command = &$command;
    }

    public function loadModelNames()
    {
        $modelNames = new ModelNames();
        $modelNames->name = $this->command->argument('model');

        if ($this->getOption('plural')) {
            $modelNames->plural = $this->getOption('plural');
        } else {
            $modelNames->plural = Str::plural($modelNames->name);
        }

        $modelNames->camel = Str::camel($modelNames->name);
        $modelNames->camelPlural = Str::camel($modelNames->plural);
        $modelNames->snake = Str::snake($modelNames->name);
        $modelNames->snakePlural = Str::snake($modelNames->plural);
        $modelNames->dashed = Str::kebab($modelNames->name);
        $modelNames->dashedPlural = Str::kebab($modelNames->plural);
        $modelNames->human = Str::title(str_replace('_', ' ', $modelNames->snake));
        $modelNames->humanPlural = Str::title(str_replace('_', ' ', $modelNames->snakePlural));

        $this->modelNames = $modelNames;
    }

    public function loadPrefixes()
    {
        $prefixes = new GeneratorPrefixes();

        $prefixes->route = config('laravel_generator.prefixes.route', '');
        $prefixes->namespace = config('laravel_generator.prefixes.namespace', '');
        $prefixes->path = config('laravel_generator.prefixes.path', '');
        $prefixes->view = config('laravel_generator.prefixes.view', '');
        $prefixes->public = config('laravel_generator.prefixes.public', '');

        if ($this->getOption('prefix')) {
            $multiplePrefixes = explode('/', $this->getOption('prefix'));

            $prefixes->mergeRoutePrefix($multiplePrefixes);
            $prefixes->mergeNamespacePrefix($multiplePrefixes);
            $prefixes->mergePathPrefix($multiplePrefixes);
            $prefixes->mergeViewPrefix($multiplePrefixes);
            $prefixes->mergePublicPrefix($multiplePrefixes);
        }

        $this->prefixes = $prefixes;
    }

    public function loadPaths()
    {
        $paths = new GeneratorPaths();

        $pathPrefix = $this->prefixes->path;
        $viewPrefix = $this->prefixes->view;

        $paths->repository = config(
            'laravel_generator.path.repository',
            app_path('Repositories/')
        ).$pathPrefix;

        $paths->model = config('laravel_generator.path.model', app_path('Models/')).$pathPrefix;

        $paths->dataTables = config(
            'laravel_generator.path.datatables',
            app_path('DataTables/')
        ).$pathPrefix;

        $paths->livewireTables = config(
            'laravel_generator.path.livewire_tables',
            app_path('Http/Livewire/')
        );

        $paths->apiController = config(
            'laravel_generator.path.api_controller',
            app_path('Http/Controllers/API/')
        ).$pathPrefix;

        $paths->apiResource = config(
            'laravel_generator.path.api_resource',
            app_path('Http/Resources/')
        ).$pathPrefix;

        $paths->apiRequest = config(
            'laravel_generator.path.api_request',
            app_path('Http/Requests/API/')
        ).$pathPrefix;

        $paths->apiRoutes = config(
            'laravel_generator.path.api_routes',
            base_path('routes/api.php')
        );

        $paths->apiTests = config('laravel_generator.path.api_test', base_path('tests/APIs/'));

        $paths->controller = config(
            'laravel_generator.path.controller',
            app_path('Http/Controllers/')
        ).$pathPrefix;

        $paths->request = config('laravel_generator.path.request', app_path('Http/Requests/')).$pathPrefix;

        $paths->routes = config('laravel_generator.path.routes', base_path('routes/web.php'));
        $paths->factory = config('laravel_generator.path.factory', database_path('factories/'));

        $paths->views = config(
            'laravel_generator.path.views',
            resource_path('views/')
        ).$viewPrefix.$this->modelNames->snakePlural.'/';

        $paths->assets = config(
            'laravel_generator.path.assets',
            resource_path('assets/')
        );

        $paths->seeder = config('laravel_generator.path.seeder', database_path('seeders/'));
        $paths->databaseSeeder = config('laravel_generator.path.database_seeder', database_path('seeders/DatabaseSeeder.php'));
        $paths->viewProvider = config(
            'laravel_generator.path.view_provider',
            app_path('Providers/ViewServiceProvider.php')
        );

        $paths->modelJsPath = config(
            'laravel_generator.path.modelsJs',
            resource_path('assets/js/models/')
        );

        $this->paths = $paths;
    }

    public function loadNamespaces()
    {
        $prefix = $this->prefixes->namespace;

        $namespaces = new GeneratorNamespaces();

        $namespaces->app = $this->command->getLaravel()->getNamespace();
        $namespaces->app = substr($namespaces->app, 0, strlen($namespaces->app) - 1);
        $namespaces->repository = config('laravel_generator.namespace.repository', 'App\Repositories').$prefix;
        $namespaces->model = config('laravel_generator.namespace.model', 'App\Models').$prefix;
        $namespaces->seeder = config('laravel_generator.namespace.seeder', 'Database\Seeders').$prefix;
        $namespaces->factory = config('laravel_generator.namespace.factory', 'Database\Factories').$prefix;
        $namespaces->dataTables = config('laravel_generator.namespace.datatables', 'App\DataTables').$prefix;
        $namespaces->livewireTables = config('laravel_generator.namespace.livewire_tables', 'App\Http\Livewire');
        $namespaces->modelExtend = config(
            'laravel_generator.model_extend_class',
            'Illuminate\Database\Eloquent\Model'
        );

        $namespaces->apiController = config(
            'laravel_generator.namespace.api_controller',
            'App\Http\Controllers\API'
        ).$prefix;
        $namespaces->apiResource = config(
            'laravel_generator.namespace.api_resource',
            'App\Http\Resources'
        ).$prefix;

        $namespaces->apiRequest = config(
            'laravel_generator.namespace.api_request',
            'App\Http\Requests\API'
        ).$prefix;

        $namespaces->request = config(
            'laravel_generator.namespace.request',
            'App\Http\Requests'
        ).$prefix;
        $namespaces->requestBase = config('laravel_generator.namespace.request', 'App\Http\Requests');
        $namespaces->baseController = config('laravel_generator.namespace.controller', 'App\Http\Controllers');
        $namespaces->controller = config(
            'laravel_generator.namespace.controller',
            'App\Http\Controllers'
        ).$prefix;

        $namespaces->apiTests = config('laravel_generator.namespace.api_test', 'Tests\APIs');
        $namespaces->repositoryTests = config('laravel_generator.namespace.repository_test', 'Tests\Repositories');
        $namespaces->tests = config('laravel_generator.namespace.tests', 'Tests');

        $this->namespaces = $namespaces;
    }

    public function loadDynamicVariables()
    {
        $this->addDynamicVariable('$NAMESPACE_APP$', $this->namespaces->app);
        $this->addDynamicVariable('$NAMESPACE_REPOSITORY$', $this->namespaces->repository);
        $this->addDynamicVariable('$NAMESPACE_MODEL$', $this->namespaces->model);
        $this->addDynamicVariable('$NAMESPACE_DATATABLES$', $this->namespaces->dataTables);
        $this->addDynamicVariable('$NAMESPACE_LIVEWIRE_TABLES$', $this->namespaces->livewireTables);
        $this->addDynamicVariable('$NAMESPACE_MODEL_EXTEND$', $this->namespaces->modelExtend);

        $this->addDynamicVariable('$NAMESPACE_SEEDER$', $this->namespaces->seeder);
        $this->addDynamicVariable('$NAMESPACE_FACTORY$', $this->namespaces->factory);

        $this->addDynamicVariable('$NAMESPACE_API_CONTROLLER$', $this->namespaces->apiController);
        $this->addDynamicVariable('$NAMESPACE_API_RESOURCE$', $this->namespaces->apiResource);
        $this->addDynamicVariable('$NAMESPACE_API_REQUEST$', $this->namespaces->apiRequest);

        $this->addDynamicVariable('$NAMESPACE_BASE_CONTROLLER$', $this->namespaces->baseController);
        $this->addDynamicVariable('$NAMESPACE_CONTROLLER$', $this->namespaces->controller);
        $this->addDynamicVariable('$NAMESPACE_REQUEST$', $this->namespaces->request);
        $this->addDynamicVariable('$NAMESPACE_REQUEST_BASE$', $this->namespaces->requestBase);

        $this->addDynamicVariable('$NAMESPACE_API_TESTS$', $this->namespaces->apiTests);
        $this->addDynamicVariable('$NAMESPACE_REPOSITORIES_TESTS$', $this->namespaces->repositoryTests);
        $this->addDynamicVariable('$NAMESPACE_TESTS$', $this->namespaces->tests);

        $this->addDynamicVariable('$TABLE_NAME$', $this->tableName);
        $this->addDynamicVariable('$TABLE_NAME_TITLE$', Str::studly($this->tableName));
        $this->addDynamicVariable('$PRIMARY_KEY_NAME$', $this->primaryName);

        $this->addDynamicVariable('$MODEL_NAME$', $this->modelNames->name);
        $this->addDynamicVariable('$MODEL_NAME_CAMEL$', $this->modelNames->camel);
        $this->addDynamicVariable('$MODEL_NAME_PLURAL$', $this->modelNames->plural);
        $this->addDynamicVariable('$MODEL_NAME_PLURAL_CAMEL$', $this->modelNames->camelPlural);
        $this->addDynamicVariable('$MODEL_NAME_SNAKE$', $this->modelNames->snake);
        $this->addDynamicVariable('$MODEL_NAME_PLURAL_SNAKE$', $this->modelNames->snakePlural);
        $this->addDynamicVariable('$MODEL_NAME_DASHED$', $this->modelNames->dashed);
        $this->addDynamicVariable('$MODEL_NAME_PLURAL_DASHED$', $this->modelNames->dashedPlural);
        $this->addDynamicVariable('$MODEL_NAME_HUMAN$', $this->modelNames->human);
        $this->addDynamicVariable('$MODEL_NAME_PLURAL_HUMAN$', $this->modelNames->humanPlural);
        $this->addDynamicVariable('$FILES$', '');

        $connectionText = '';
        if ($connection = $this->getOption('connection')) {
            $this->connection = $connection;
            $connectionText = infy_tabs(4).'public $connection = "'.$connection.'";';
        }
        $this->addDynamicVariable('$CONNECTION$', $connectionText);

        $this->addDynamicVariable('$PRIMARY_KEY_NAME$', $this->primaryName);

        if (!empty($this->prefixes->route)) {
            $this->addDynamicVariable('$ROUTE_NAMED_PREFIX$', $this->prefixes->route.'.');
            $this->addDynamicVariable('$ROUTE_PREFIX$', str_replace('.', '/', $this->prefixes->route).'/');
            $this->addDynamicVariable('$RAW_ROUTE_PREFIX$', $this->prefixes->route);
        } else {
            $this->addDynamicVariable('$ROUTE_PREFIX$', '');
            $this->addDynamicVariable('$ROUTE_NAMED_PREFIX$', '');
        }

        if (!empty($this->prefixes->namespace)) {
            $this->addDynamicVariable('$PATH_PREFIX$', $this->prefixes->namespace.'\\');
        } else {
            $this->addDynamicVariable('$PATH_PREFIX$', '');
        }

        if (!empty($this->prefixes->view)) {
            $this->addDynamicVariable('$VIEW_PREFIX$', str_replace('/', '.', $this->prefixes->view).'.');
        } else {
            $this->addDynamicVariable('$VIEW_PREFIX$', '');
        }

        if (!empty($this->prefixes->public)) {
            $this->addDynamicVariable('$PUBLIC_PREFIX$', $this->prefixes->public);
        } else {
            $this->addDynamicVariable('$PUBLIC_PREFIX$', '');
        }

        $this->addDynamicVariable(
            '$API_PREFIX$',
            config('laravel_generator.api_prefix', 'api')
        );

        $this->addDynamicVariable('$SEARCHABLE$', '');
    }

    public function prepareTableName()
    {
        if ($this->getOption('table')) {
            $this->tableName = $this->getOption('table');
        } else {
            $this->tableName = $this->modelNames->snakePlural;
        }
    }

    public function preparePrimaryName()
    {
        if ($this->getOption('primary')) {
            $this->primaryName = $this->getOption('primary');
        } else {
            $this->primaryName = 'id';
        }
    }

    public function prepareOptions()
    {
        $options = new GeneratorOptions();

        $options->softDelete = config('laravel_generator.options.soft_delete', false);
        $options->saveSchemaFile = config('laravel_generator.options.save_schema_file', true);
        $options->localized = config('laravel_generator.options.localized', false);
        $options->repositoryPattern = config('laravel_generator.options.repository_pattern', true);
        $options->resources = config('laravel_generator.options.resources', false);
        $options->factory = config('laravel_generator.options.factory', false);
        $options->seeder = config('laravel_generator.options.seeder', false);
        $options->excludedFields = config('laravel_generator.options.excluded_fields', ['id']);

        $this->options = $options;
    }

    public function prepareAddons()
    {
        $addons = new GeneratorAddons();
        $addons->swagger = config('laravel_generator.add_on.swagger', false);
        $addons->tests = config('laravel_generator.add_on.tests', false);

        $this->addons = $addons;
    }

    public function overrideOptionsFromJsonFile($jsonData)
    {
//        $options = self::$availableOptions;
//
//        foreach ($options as $option) {
//            if (isset($jsonData['options'][$option])) {
//                $this->setOption($option, $jsonData['options'][$option]);
//            }
//        }
//
//        // prepare prefixes than reload namespaces, paths and dynamic variables
//        if (!empty($this->getOption('prefix'))) {
//            $this->preparePrefixes();
//            $this->loadPaths();
//            $this->loadNamespaces();
//            $this->loadDynamicVariables();
//        }
//
//        $addOns = ['swagger', 'tests', 'datatables'];
//
//        foreach ($addOns as $addOn) {
//            if (isset($jsonData['addOns'][$addOn])) {
//                $this->addOns[$addOn] = $jsonData['addOns'][$addOn];
//            }
//        }
    }

    public function getOption($option)
    {
        return $this->command->option($option);
    }

    public function isLocalizedTemplates()
    {
        return $this->options->localized;
    }

    public function addDynamicVariable($name, $val)
    {
        $this->dynamicVars[$name] = $val;
    }

    public function commandError($error)
    {
        $this->command->error($error);
    }

    public function commandComment($message)
    {
        $this->command->comment($message);
    }

    public function commandWarn($warning)
    {
        $this->command->warn($warning);
    }

    public function commandInfo($message)
    {
        $this->command->info($message);
    }
}

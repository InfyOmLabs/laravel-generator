<?php

namespace InfyOm\Generator\Common;

use Illuminate\Support\Str;

class GeneratorConfig
{
    /* Namespace variables */
    public $nsApp;
    public $nsRepository;
    public $nsModel;
    public $nsDataTables;
    public $nsModelExtend;

    public $nsSeeder;
    public $nsFactory;

    public $nsApiController;
    public $nsApiResource;
    public $nsApiRequest;

    public $nsRequest;
    public $nsRequestBase;
    public $nsController;
    public $nsBaseController;

    public $nsApiTests;
    public $nsRepositoryTests;
    public $nsTestTraits;
    public $nsTests;

    /* Path variables */
    public $pathRepository;
    public $pathModel;
    public $pathDataTables;
    public $pathFactory;
    public $pathSeeder;
    public $pathDatabaseSeeder;
    public $pathViewProvider;

    public $pathApiController;
    public $pathApiResource;
    public $pathApiRequest;
    public $pathApiRoutes;
    public $pathApiTests;

    public $pathController;
    public $pathRequest;
    public $pathRoutes;
    public $pathViews;
    public $pathAssets;
    public $modelJsPath;

    /* Model Names */
    public $mName;
    public $mPlural;
    public $mCamel;
    public $mCamelPlural;
    public $mSnake;
    public $mSnakePlural;
    public $mDashed;
    public $mDashedPlural;
    public $mSlash;
    public $mSlashPlural;
    public $mHuman;
    public $mHumanPlural;

    public $connection = '';

    /* Generator Options */
    public $options;

    /* Prefixes */
    public $prefixes;

    /** @var CommandData */
    private $commandData;

    /* Command Options */
    public static $availableOptions = [
        'fieldsFile',
        'jsonFromGUI',
        'tableName',
        'fromTable',
        'ignoreFields',
        'save',
        'primary',
        'prefix',
        'paginate',
        'skip',
        'datatables',
        'views',
        'relations',
        'plural',
        'softDelete',
        'forceMigrate',
        'factory',
        'seeder',
        'repositoryPattern',
        'resources',
        'localized',
        'connection',
        'jqueryDT',
    ];

    public $tableName;

    /** @var string */
    protected $primaryName;

    /* Generator AddOns */
    public $addOns;

    public function init(CommandData &$commandData, $options = null)
    {
        if (!empty($options)) {
            self::$availableOptions = $options;
        }

        $this->mName = $commandData->modelName;

        $this->prepareAddOns();
        $this->prepareOptions($commandData);
        $this->prepareModelNames();
        $this->preparePrefixes();
        $this->loadPaths();
        $this->prepareTableName();
        $this->preparePrimaryName();
        $this->loadNamespaces($commandData);
        $commandData = $this->loadDynamicVariables($commandData);
        $this->commandData = &$commandData;
    }

    public function loadNamespaces(CommandData &$commandData)
    {
        $prefix = $this->prefixes['ns'];

        if (!empty($prefix)) {
            $prefix = '\\'.$prefix;
        }

        $this->nsApp = $commandData->commandObj->getLaravel()->getNamespace();
        $this->nsApp = substr($this->nsApp, 0, strlen($this->nsApp) - 1);
        $this->nsRepository = config('infyom.laravel_generator.namespace.repository', 'App\Repositories').$prefix;
        $this->nsModel = config('infyom.laravel_generator.namespace.model', 'App\Models').$prefix;
        if (config('infyom.laravel_generator.ignore_model_prefix', false)) {
            $this->nsModel = config('infyom.laravel_generator.namespace.model', 'App\Models');
        }
        $this->nsSeeder = config('infyom.laravel_generator.namespace.seeder', 'Database\Seeders').$prefix;
        $this->nsFactory = config('infyom.laravel_generator.namespace.factory', 'Database\Factories').$prefix;
        $this->nsDataTables = config('infyom.laravel_generator.namespace.datatables', 'App\DataTables').$prefix;
        $this->nsModelExtend = config(
            'infyom.laravel_generator.model_extend_class',
            'Illuminate\Database\Eloquent\Model'
        );

        $this->nsApiController = config(
            'infyom.laravel_generator.namespace.api_controller',
            'App\Http\Controllers\API'
        ).$prefix;
        $this->nsApiResource = config(
            'infyom.laravel_generator.namespace.api_resource',
            'App\Http\Resources'
        ).$prefix;
        $this->nsApiRequest = config('infyom.laravel_generator.namespace.api_request', 'App\Http\Requests\API').$prefix;

        $this->nsRequest = config('infyom.laravel_generator.namespace.request', 'App\Http\Requests').$prefix;
        $this->nsRequestBase = config('infyom.laravel_generator.namespace.request', 'App\Http\Requests');
        $this->nsBaseController = config('infyom.laravel_generator.namespace.controller', 'App\Http\Controllers');
        $this->nsController = config('infyom.laravel_generator.namespace.controller', 'App\Http\Controllers').$prefix;

        $this->nsApiTests = config('infyom.laravel_generator.namespace.api_test', 'Tests\APIs');
        $this->nsRepositoryTests = config('infyom.laravel_generator.namespace.repository_test', 'Tests\Repositories');
        $this->nsTests = config('infyom.laravel_generator.namespace.tests', 'Tests');
    }

    public function loadPaths()
    {
        $prefix = $this->prefixes['path'];

        if (!empty($prefix)) {
            $prefix .= '/';
        }

        $viewPrefix = $this->prefixes['view'];

        if (!empty($viewPrefix)) {
            $viewPrefix .= '/';
        }

        $this->pathRepository = config(
            'infyom.laravel_generator.path.repository',
            app_path('Repositories/')
        ).$prefix;

        $this->pathModel = config('infyom.laravel_generator.path.model', app_path('Models/')).$prefix;
        if (config('infyom.laravel_generator.ignore_model_prefix', false)) {
            $this->pathModel = config('infyom.laravel_generator.path.model', app_path('Models/'));
        }

        $this->pathDataTables = config('infyom.laravel_generator.path.datatables', app_path('DataTables/')).$prefix;

        $this->pathApiController = config(
            'infyom.laravel_generator.path.api_controller',
            app_path('Http/Controllers/API/')
        ).$prefix;

        $this->pathApiResource = config(
            'infyom.laravel_generator.path.api_resource',
            app_path('Http/Resources/')
        ).$prefix;

        $this->pathApiRequest = config(
            'infyom.laravel_generator.path.api_request',
            app_path('Http/Requests/API/')
        ).$prefix;

        $this->pathApiRoutes = config('infyom.laravel_generator.path.api_routes', base_path('routes/api.php'));

        $this->pathApiTests = config('infyom.laravel_generator.path.api_test', base_path('tests/APIs/'));

        $this->pathController = config(
            'infyom.laravel_generator.path.controller',
            app_path('Http/Controllers/')
        ).$prefix;

        $this->pathRequest = config('infyom.laravel_generator.path.request', app_path('Http/Requests/')).$prefix;

        $this->pathRoutes = config('infyom.laravel_generator.path.routes', base_path('routes/web.php'));
        $this->pathFactory = config('infyom.laravel_generator.path.factory', database_path('factories/'));

        $this->pathViews = config(
            'infyom.laravel_generator.path.views',
            resource_path('views/')
        ).$viewPrefix.$this->mSnakePlural.'/';

        $this->pathAssets = config(
            'infyom.laravel_generator.path.assets',
            resource_path('assets/')
        );

        $this->pathSeeder = config('infyom.laravel_generator.path.seeder', database_path('seeders/'));
        $this->pathDatabaseSeeder = config('infyom.laravel_generator.path.database_seeder', database_path('seeders/DatabaseSeeder.php'));
        $this->pathViewProvider = config(
            'infyom.laravel_generator.path.view_provider',
            app_path('Providers/ViewServiceProvider.php')
        );

        $this->modelJsPath = config(
            'infyom.laravel_generator.path.modelsJs',
            resource_path('assets/js/models/')
        );
    }

    public function loadDynamicVariables(CommandData &$commandData)
    {
        $commandData->addDynamicVariable('$NAMESPACE_APP$', $this->nsApp);
        $commandData->addDynamicVariable('$NAMESPACE_REPOSITORY$', $this->nsRepository);
        $commandData->addDynamicVariable('$NAMESPACE_MODEL$', $this->nsModel);
        $commandData->addDynamicVariable('$NAMESPACE_DATATABLES$', $this->nsDataTables);
        $commandData->addDynamicVariable('$NAMESPACE_MODEL_EXTEND$', $this->nsModelExtend);

        $commandData->addDynamicVariable('$NAMESPACE_SEEDER$', $this->nsSeeder);
        $commandData->addDynamicVariable('$NAMESPACE_FACTORY$', $this->nsFactory);

        $commandData->addDynamicVariable('$NAMESPACE_API_CONTROLLER$', $this->nsApiController);
        $commandData->addDynamicVariable('$NAMESPACE_API_RESOURCE$', $this->nsApiResource);
        $commandData->addDynamicVariable('$NAMESPACE_API_REQUEST$', $this->nsApiRequest);

        $commandData->addDynamicVariable('$NAMESPACE_BASE_CONTROLLER$', $this->nsBaseController);
        $commandData->addDynamicVariable('$NAMESPACE_CONTROLLER$', $this->nsController);
        $commandData->addDynamicVariable('$NAMESPACE_REQUEST$', $this->nsRequest);
        $commandData->addDynamicVariable('$NAMESPACE_REQUEST_BASE$', $this->nsRequestBase);

        $commandData->addDynamicVariable('$NAMESPACE_API_TESTS$', $this->nsApiTests);
        $commandData->addDynamicVariable('$NAMESPACE_REPOSITORIES_TESTS$', $this->nsRepositoryTests);
        $commandData->addDynamicVariable('$NAMESPACE_TESTS$', $this->nsTests);

        $commandData->addDynamicVariable('$TABLE_NAME$', $this->tableName);
        $commandData->addDynamicVariable('$TABLE_NAME_TITLE$', Str::studly($this->tableName));
        $commandData->addDynamicVariable('$PRIMARY_KEY_NAME$', $this->primaryName);

        $commandData->addDynamicVariable('$MODEL_NAME$', $this->mName);
        $commandData->addDynamicVariable('$MODEL_NAME_CAMEL$', $this->mCamel);
        $commandData->addDynamicVariable('$MODEL_NAME_PLURAL$', $this->mPlural);
        $commandData->addDynamicVariable('$MODEL_NAME_PLURAL_CAMEL$', $this->mCamelPlural);
        $commandData->addDynamicVariable('$MODEL_NAME_SNAKE$', $this->mSnake);
        $commandData->addDynamicVariable('$MODEL_NAME_PLURAL_SNAKE$', $this->mSnakePlural);
        $commandData->addDynamicVariable('$MODEL_NAME_DASHED$', $this->mDashed);
        $commandData->addDynamicVariable('$MODEL_NAME_PLURAL_DASHED$', $this->mDashedPlural);
        $commandData->addDynamicVariable('$MODEL_NAME_SLASH$', $this->mSlash);
        $commandData->addDynamicVariable('$MODEL_NAME_PLURAL_SLASH$', $this->mSlashPlural);
        $commandData->addDynamicVariable('$MODEL_NAME_HUMAN$', $this->mHuman);
        $commandData->addDynamicVariable('$MODEL_NAME_PLURAL_HUMAN$', $this->mHumanPlural);
        $commandData->addDynamicVariable('$FILES$', '');

        $connectionText = '';
        if ($connection = $this->getOption('connection')) {
            $this->connection = $connection;
            $connectionText = infy_tab(4).'public $connection = "'.$connection.'";';
        }
        $commandData->addDynamicVariable('$CONNECTION$', $connectionText);

        if (!empty($this->prefixes['route'])) {
            $commandData->addDynamicVariable('$ROUTE_NAMED_PREFIX$', $this->prefixes['route'].'.');
            $commandData->addDynamicVariable('$ROUTE_PREFIX$', str_replace('.', '/', $this->prefixes['route']).'/');
            $commandData->addDynamicVariable('$RAW_ROUTE_PREFIX$', $this->prefixes['route']);
        } else {
            $commandData->addDynamicVariable('$ROUTE_PREFIX$', '');
            $commandData->addDynamicVariable('$ROUTE_NAMED_PREFIX$', '');
        }

        if (!empty($this->prefixes['ns'])) {
            $commandData->addDynamicVariable('$PATH_PREFIX$', $this->prefixes['ns'].'\\');
        } else {
            $commandData->addDynamicVariable('$PATH_PREFIX$', '');
        }

        if (!empty($this->prefixes['view'])) {
            $commandData->addDynamicVariable('$VIEW_PREFIX$', str_replace('/', '.', $this->prefixes['view']).'.');
        } else {
            $commandData->addDynamicVariable('$VIEW_PREFIX$', '');
        }

        if (!empty($this->prefixes['public'])) {
            $commandData->addDynamicVariable('$PUBLIC_PREFIX$', $this->prefixes['public']);
        } else {
            $commandData->addDynamicVariable('$PUBLIC_PREFIX$', '');
        }

        $commandData->addDynamicVariable(
            '$API_PREFIX$',
            config('infyom.laravel_generator.api_prefix', 'api')
        );

        $commandData->addDynamicVariable(
            '$API_VERSION$',
            config('infyom.laravel_generator.api_version', 'v1')
        );

        $commandData->addDynamicVariable('$SEARCHABLE$', '');

        return $commandData;
    }

    public function prepareTableName()
    {
        if ($this->getOption('tableName')) {
            $this->tableName = $this->getOption('tableName');
        } else {
            $this->tableName = $this->mSnakePlural;
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

    public function prepareModelNames()
    {
        if ($this->getOption('plural')) {
            $this->mPlural = $this->getOption('plural');
        } else {
            $this->mPlural = Str::plural($this->mName);
        }
        $this->mCamel = Str::camel($this->mName);
        $this->mCamelPlural = Str::camel($this->mPlural);
        $this->mSnake = Str::snake($this->mName);
        $this->mSnakePlural = Str::snake($this->mPlural);
        $this->mDashed = str_replace('_', '-', Str::snake($this->mSnake));
        $this->mDashedPlural = str_replace('_', '-', Str::snake($this->mSnakePlural));
        $this->mSlash = str_replace('_', '/', Str::snake($this->mSnake));
        $this->mSlashPlural = str_replace('_', '/', Str::snake($this->mSnakePlural));
        $this->mHuman = Str::title(str_replace('_', ' ', Str::snake($this->mSnake)));
        $this->mHumanPlural = Str::title(str_replace('_', ' ', Str::snake($this->mSnakePlural)));
    }

    public function prepareOptions(CommandData &$commandData)
    {
        foreach (self::$availableOptions as $option) {
            $this->options[$option] = $commandData->commandObj->option($option);
        }

        if (isset($options['fromTable']) and $this->options['fromTable']) {
            if (!$this->options['tableName']) {
                $commandData->commandError('tableName required with fromTable option.');
                exit;
            }
        }

        if (empty($this->options['save'])) {
            $this->options['save'] = config('infyom.laravel_generator.options.save_schema_file', true);
        }

        if (empty($this->options['localized'])) {
            $this->options['localized'] = config('infyom.laravel_generator.options.localized', false);
        }

        if ($this->options['localized']) {
            $commandData->getTemplatesManager()->setUseLocale(true);
        }

        $this->options['softDelete'] = config('infyom.laravel_generator.options.softDelete', false);
        $this->options['repositoryPattern'] = config('infyom.laravel_generator.options.repository_pattern', true);
        $this->options['resources'] = config('infyom.laravel_generator.options.resources', true);
        if (!empty($this->options['skip'])) {
            $this->options['skip'] = array_map('trim', explode(',', $this->options['skip']));
        }

        if (!empty($this->options['datatables'])) {
            if (strtolower($this->options['datatables']) == 'true') {
                $this->addOns['datatables'] = true;
            } else {
                $this->addOns['datatables'] = false;
            }
        }
    }

    public function preparePrefixes()
    {
        $this->prefixes['route'] = explode('/', config('infyom.laravel_generator.prefixes.route', ''));
        $this->prefixes['path'] = explode('/', config('infyom.laravel_generator.prefixes.path', ''));
        $this->prefixes['view'] = explode('.', config('infyom.laravel_generator.prefixes.view', ''));
        $this->prefixes['public'] = explode('/', config('infyom.laravel_generator.prefixes.public', ''));

        if ($this->getOption('prefix')) {
            $multiplePrefixes = explode('/', $this->getOption('prefix'));

            $this->prefixes['route'] = array_merge($this->prefixes['route'], $multiplePrefixes);
            $this->prefixes['path'] = array_merge($this->prefixes['path'], $multiplePrefixes);
            $this->prefixes['view'] = array_merge($this->prefixes['view'], $multiplePrefixes);
            $this->prefixes['public'] = array_merge($this->prefixes['public'], $multiplePrefixes);
        }

        $this->prefixes['route'] = array_diff($this->prefixes['route'], ['']);
        $this->prefixes['path'] = array_diff($this->prefixes['path'], ['']);
        $this->prefixes['view'] = array_diff($this->prefixes['view'], ['']);
        $this->prefixes['public'] = array_diff($this->prefixes['public'], ['']);

        $routePrefix = '';

        foreach ($this->prefixes['route'] as $singlePrefix) {
            $routePrefix .= Str::camel($singlePrefix).'.';
        }

        if (!empty($routePrefix)) {
            $routePrefix = substr($routePrefix, 0, strlen($routePrefix) - 1);
        }

        $this->prefixes['route'] = $routePrefix;

        $nsPrefix = '';

        foreach ($this->prefixes['path'] as $singlePrefix) {
            $nsPrefix .= Str::title($singlePrefix).'\\';
        }

        if (!empty($nsPrefix)) {
            $nsPrefix = substr($nsPrefix, 0, strlen($nsPrefix) - 1);
        }

        $this->prefixes['ns'] = $nsPrefix;

        $pathPrefix = '';

        foreach ($this->prefixes['path'] as $singlePrefix) {
            $pathPrefix .= Str::title($singlePrefix).'/';
        }

        if (!empty($pathPrefix)) {
            $pathPrefix = substr($pathPrefix, 0, strlen($pathPrefix) - 1);
        }

        $this->prefixes['path'] = $pathPrefix;

        $viewPrefix = '';

        foreach ($this->prefixes['view'] as $singlePrefix) {
            $viewPrefix .= Str::camel($singlePrefix).'/';
        }

        if (!empty($viewPrefix)) {
            $viewPrefix = substr($viewPrefix, 0, strlen($viewPrefix) - 1);
        }

        $this->prefixes['view'] = $viewPrefix;

        $publicPrefix = '';

        foreach ($this->prefixes['public'] as $singlePrefix) {
            $publicPrefix .= Str::camel($singlePrefix).'/';
        }

        if (!empty($publicPrefix)) {
            $publicPrefix = substr($publicPrefix, 0, strlen($publicPrefix) - 1);
        }

        $this->prefixes['public'] = $publicPrefix;
    }

    public function overrideOptionsFromJsonFile($jsonData)
    {
        $options = self::$availableOptions;

        foreach ($options as $option) {
            if (isset($jsonData['options'][$option])) {
                $this->setOption($option, $jsonData['options'][$option]);
            }
        }

        // prepare prefixes than reload namespaces, paths and dynamic variables
        if (!empty($this->getOption('prefix'))) {
            $this->preparePrefixes();
            $this->loadPaths();
            $this->loadNamespaces($this->commandData);
            $this->loadDynamicVariables($this->commandData);
        }

        $addOns = ['swagger', 'tests', 'datatables'];

        foreach ($addOns as $addOn) {
            if (isset($jsonData['addOns'][$addOn])) {
                $this->addOns[$addOn] = $jsonData['addOns'][$addOn];
            }
        }
    }

    public function getOption($option)
    {
        if (isset($this->options[$option])) {
            return $this->options[$option];
        }

        return false;
    }

    public function getAddOn($addOn)
    {
        if (isset($this->addOns[$addOn])) {
            return $this->addOns[$addOn];
        }

        return false;
    }

    public function setOption($option, $value)
    {
        $this->options[$option] = $value;
    }

    public function prepareAddOns()
    {
        $this->addOns['swagger'] = config('infyom.laravel_generator.add_on.swagger', false);
        $this->addOns['tests'] = config('infyom.laravel_generator.add_on.tests', false);
        $this->addOns['datatables'] = config('infyom.laravel_generator.add_on.datatables', false);
        $this->addOns['menu.enabled'] = config('infyom.laravel_generator.add_on.menu.enabled', false);
        $this->addOns['menu.menu_file'] = config('infyom.laravel_generator.add_on.menu.menu_file', 'layouts.menu');
    }
}

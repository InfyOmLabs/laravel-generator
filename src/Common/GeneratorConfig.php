<?php

namespace InfyOm\Generator\Common;

use Illuminate\Support\Str;

class GeneratorConfig
{
    /* Namespace variables */
    public $nsApp;
    public $nsRepository;
    public $nsModel;
    public $nsModelExtend;

    public $nsApiController;
    public $nsApiRequest;

    public $nsRequest;
    public $nsRequestBase;
    public $nsController;

    /* Path variables */
    public $pathRepository;
    public $pathModel;

    public $pathApiController;
    public $pathApiRequest;
    public $pathApiRoutes;
    public $pathApiTests;
    public $pathApiTestTraits;

    public $pathController;
    public $pathRequest;
    public $pathRoutes;
    public $pathViews;

    /* Model Names */
    public $mName;
    public $mPlural;
    public $mCamel;
    public $mCamelPlural;
    public $mSnake;
    public $mSnakePlural;

    /* Generator Options */
    public $options;

    /* Generator AddOns */
    public $addOns;

    public function init(CommandData &$commandData)
    {
        $this->mName = $commandData->modelName;

        $this->prepareOptions($commandData);
        $this->prepareAddOns();
        $this->prepareModelNames();
        $this->loadNamespaces($commandData);
        $this->loadPaths();
        $commandData = $this->loadDynamicVariables($commandData);
    }

    public function loadNamespaces(CommandData &$commandData)
    {
        $prefix = $this->getOption('prefix');

        if (!empty($prefix)) {
            $prefix = '\\'.Str::title($prefix);
        }

        $this->nsApp = $commandData->commandObj->getLaravel()->getNamespace();
        $this->nsRepository = config('infyom.laravel_generator.namespace.repository', 'App\Repositories').$prefix;
        $this->nsModel = config('infyom.laravel_generator.namespace.model', 'App\Models').$prefix;
        $this->nsModelExtend = config(
            'infyom.laravel_generator.model_extend_class',
            'Illuminate\Database\Eloquent\Model'
        );

        $this->nsApiController = config(
            'infyom.laravel_generator.namespace.api_controller',
            'App\Http\Controllers\API'
        ).$prefix;
        $this->nsApiRequest = config('infyom.laravel_generator.namespace.api_request', 'App\Http\Requests\API').$prefix;

        $this->nsRequest = config('infyom.laravel_generator.namespace.request', 'App\Http\Requests').$prefix;
        $this->nsRequestBase = config('infyom.laravel_generator.namespace.request', 'App\Http\Requests');
        $this->nsController = config('infyom.laravel_generator.namespace.controller', 'App\Http\Controllers').$prefix;
    }

    public function loadPaths()
    {
        $prefix = $this->getOption('prefix');

        if (!empty($prefix)) {
            $prefixTitle = Str::title($prefix).'/';
        } else {
            $prefixTitle = '';
        }

        $this->pathRepository = config(
            'infyom.laravel_generator.path.repository',
            app_path('Repositories/')
        ).$prefixTitle;

        $this->pathModel = config('infyom.laravel_generator.path.model', app_path('Models/')).$prefixTitle;

        $this->pathApiController = config(
            'infyom.laravel_generator.path.api_controller',
            app_path('Http/Controllers/API/')
        ).$prefixTitle;

        $this->pathApiRequest = config(
            'infyom.laravel_generator.path.api_request',
            app_path('Http/Requests/API/')
        ).$prefixTitle;

        $this->pathApiRoutes = config('infyom.laravel_generator.path.api_routes', app_path('Http/api_routes.php'));

        $this->pathApiTests = config('infyom.laravel_generator.path.api_test', base_path('tests/'));

        $this->pathApiTestTraits = config('infyom.laravel_generator.path.test_trait', base_path('tests/traits/'));

        $this->pathController = config(
            'infyom.laravel_generator.path.controller',
            app_path('Http/Controllers/')
        ).$prefixTitle;

        $this->pathRequest = config('infyom.laravel_generator.path.request', app_path('Http/Requests/')).$prefixTitle;

        $this->pathRoutes = config('infyom.laravel_generator.path.routes', app_path('Http/routes.php'));

        $this->pathViews = config(
            'infyom.laravel_generator.path.views',
            base_path('resources/views/')
        ).$prefix.'/'.$this->mCamelPlural.'/';
    }

    public function loadDynamicVariables(CommandData &$commandData)
    {
        $commandData->addDynamicVariable('$NAMESPACE_APP$', $this->nsApp);
        $commandData->addDynamicVariable('$NAMESPACE_REPOSITORY$', $this->nsRepository);
        $commandData->addDynamicVariable('$NAMESPACE_MODEL$', $this->nsModel);
        $commandData->addDynamicVariable('$NAMESPACE_MODEL_EXTEND$', $this->nsModelExtend);

        $commandData->addDynamicVariable('$NAMESPACE_API_CONTROLLER$', $this->nsApiController);
        $commandData->addDynamicVariable('$NAMESPACE_API_REQUEST$', $this->nsApiRequest);

        $commandData->addDynamicVariable('$NAMESPACE_CONTROLLER$', $this->nsController);
        $commandData->addDynamicVariable('$NAMESPACE_REQUEST$', $this->nsRequest);
        $commandData->addDynamicVariable('$NAMESPACE_REQUEST_BASE$', $this->nsRequestBase);

        if ($this->getOption('tableName')) {
            $tableName = $this->getOption('tableName');
        } else {
            $tableName = $this->mSnakePlural;
        }

        $commandData->addDynamicVariable('$TABLE_NAME$', $tableName);

        $commandData->addDynamicVariable('$MODEL_NAME$', $this->mName);
        $commandData->addDynamicVariable('$MODEL_NAME_CAMEL$', $this->mCamel);
        $commandData->addDynamicVariable('$MODEL_NAME_PLURAL$', $this->mPlural);
        $commandData->addDynamicVariable('$MODEL_NAME_PLURAL_CAMEL$', $this->mCamelPlural);
        $commandData->addDynamicVariable('$MODEL_NAME_SNAKE$', $this->mSnake);
        $commandData->addDynamicVariable('$MODEL_NAME_PLURAL_SNAKE$', $this->mSnakePlural);

        if ($this->getOption('prefix')) {
            $prefixRoutes = $this->getOption('prefix').'/';
            $prefixTitle = Str::title($this->getOption('prefix')).'\\';
            $prefixAs = $this->getOption('prefix').'.';
        } else {
            $prefixRoutes = '';
            $prefixTitle = '';
            $prefixAs = '';
        }

        $commandData->addDynamicVariable('$ROUTES_PREFIX$', $prefixRoutes);
        $commandData->addDynamicVariable('$NS_PREFIX$', $prefixTitle);
        $commandData->addDynamicVariable('$ROUTES_AS_PREFIX$', $prefixAs);

        $commandData->addDynamicVariable(
            '$MODEL_NAME_PLURAL_SNAKE$',
            config('infyom.laravel_generator.api_prefix', 'api')
        );

        $commandData->addDynamicVariable(
            '$API_VERSION$',
            config('infyom.laravel_generator.api_version', 'v1')
        );

        return $commandData;
    }

    public function prepareModelNames()
    {
        $this->mPlural = Str::plural($this->mName);
        $this->mCamel = Str::camel($this->mName);
        $this->mCamelPlural = Str::camel($this->mPlural);
        $this->mSnake = Str::snake($this->mName);
        $this->mSnakePlural = Str::snake($this->mPlural);
    }

    public function prepareOptions(CommandData &$commandData)
    {
        $options = ['fieldsFile', 'tableName', 'fromTable', 'save', 'primary', 'prefix', 'paginate'];

        foreach ($options as $option) {
            $this->options[$option] = $commandData->commandObj->option($option);
        }

        if ($this->options['fromTable']) {
            if (!$this->options['tableName']) {
                $commandData->commandError('tableName required with fromTable option.');
                exit;
            }
        }

        $this->options['softDelete'] = config('infyom.laravel_generator.options.softDelete', false);
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
    }
}

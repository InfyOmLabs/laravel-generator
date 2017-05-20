<?php

namespace InfyOm\Generator;

use Illuminate\Support\ServiceProvider;
use InfyOm\Generator\Commands\API\APIControllerGeneratorCommand;
use InfyOm\Generator\Commands\API\APIGeneratorCommand;
use InfyOm\Generator\Commands\API\APIRequestsGeneratorCommand;
use InfyOm\Generator\Commands\API\TestsGeneratorCommand;
use InfyOm\Generator\Commands\APIScaffoldGeneratorCommand;
use InfyOm\Generator\Commands\Common\MigrationGeneratorCommand;
use InfyOm\Generator\Commands\Common\ModelGeneratorCommand;
use InfyOm\Generator\Commands\Common\RepositoryGeneratorCommand;
use InfyOm\Generator\Commands\Publish\GeneratorPublishCommand;
use InfyOm\Generator\Commands\Publish\LayoutPublishCommand;
use InfyOm\Generator\Commands\Publish\PublishTemplateCommand;
use InfyOm\Generator\Commands\Publish\VueJsLayoutPublishCommand;
use InfyOm\Generator\Commands\RollbackGeneratorCommand;
use InfyOm\Generator\Commands\Scaffold\ControllerGeneratorCommand;
use InfyOm\Generator\Commands\Scaffold\RequestsGeneratorCommand;
use InfyOm\Generator\Commands\Scaffold\ScaffoldGeneratorCommand;
use InfyOm\Generator\Commands\Scaffold\ViewsGeneratorCommand;
use InfyOm\Generator\Commands\VueJs\VueJsGeneratorCommand;

class InfyOmGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__.'/../config/laravel_generator.php';

        $this->publishes([
            $configPath => config_path('infyom/laravel_generator.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('infyom.publish', function ($app) {
            return new GeneratorPublishCommand();
        });

        $this->app->singleton('infyom.api', function ($app) {
            return new APIGeneratorCommand();
        });

        $this->app->singleton('infyom.scaffold', function ($app) {
            return new ScaffoldGeneratorCommand();
        });

        $this->app->singleton('infyom.publish.layout', function ($app) {
            return new LayoutPublishCommand();
        });

        $this->app->singleton('infyom.publish.templates', function ($app) {
            return new PublishTemplateCommand();
        });

        $this->app->singleton('infyom.api_scaffold', function ($app) {
            return new APIScaffoldGeneratorCommand();
        });

        $this->app->singleton('infyom.migration', function ($app) {
            return new MigrationGeneratorCommand();
        });

        $this->app->singleton('infyom.model', function ($app) {
            return new ModelGeneratorCommand();
        });

        $this->app->singleton('infyom.repository', function ($app) {
            return new RepositoryGeneratorCommand();
        });

        $this->app->singleton('infyom.api.controller', function ($app) {
            return new APIControllerGeneratorCommand();
        });

        $this->app->singleton('infyom.api.requests', function ($app) {
            return new APIRequestsGeneratorCommand();
        });

        $this->app->singleton('infyom.api.tests', function ($app) {
            return new TestsGeneratorCommand();
        });

        $this->app->singleton('infyom.scaffold.controller', function ($app) {
            return new ControllerGeneratorCommand();
        });

        $this->app->singleton('infyom.scaffold.requests', function ($app) {
            return new RequestsGeneratorCommand();
        });

        $this->app->singleton('infyom.scaffold.views', function ($app) {
            return new ViewsGeneratorCommand();
        });

        $this->app->singleton('infyom.rollback', function ($app) {
            return new RollbackGeneratorCommand();
        });

        $this->app->singleton('infyom.vuejs', function ($app) {
            return new VueJsGeneratorCommand();
        });
        $this->app->singleton('infyom.publish.vuejs', function ($app) {
            return new VueJsLayoutPublishCommand();
        });

        $this->commands([
            'infyom.publish',
            'infyom.api',
            'infyom.scaffold',
            'infyom.api_scaffold',
            'infyom.publish.layout',
            'infyom.publish.templates',
            'infyom.migration',
            'infyom.model',
            'infyom.repository',
            'infyom.api.controller',
            'infyom.api.requests',
            'infyom.api.tests',
            'infyom.scaffold.controller',
            'infyom.scaffold.requests',
            'infyom.scaffold.views',
            'infyom.rollback',
            'infyom.vuejs',
            'infyom.publish.vuejs',
        ]);
    }
}

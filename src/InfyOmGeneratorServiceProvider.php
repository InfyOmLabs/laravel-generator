<?php

namespace InfyOm\Generator;

use Illuminate\Support\ServiceProvider;
use InfyOm\Generator\Commands\API\APIControllerGeneratorCommand;
use InfyOm\Generator\Commands\API\APIGeneratorCommand;
use InfyOm\Generator\Commands\API\APIGeneratorPublisherCommand;
use InfyOm\Generator\Commands\API\APIRequestsGeneratorCommand;
use InfyOm\Generator\Commands\API\TestCasesPublisherCommand;
use InfyOm\Generator\Commands\API\TestsGeneratorCommand;
use InfyOm\Generator\Commands\APIScaffoldGeneratorCommand;
use InfyOm\Generator\Commands\Common\MigrationGeneratorCommand;
use InfyOm\Generator\Commands\Common\ModelGeneratorCommand;
use InfyOm\Generator\Commands\Common\RepositoryGeneratorCommand;
use InfyOm\Generator\Commands\PublishTemplateCommand;
use InfyOm\Generator\Commands\Scaffold\AuthPublishCommand;
use InfyOm\Generator\Commands\Scaffold\ControllerGeneratorCommand;
use InfyOm\Generator\Commands\Scaffold\RequestsGeneratorCommand;
use InfyOm\Generator\Commands\Scaffold\ScaffoldGeneratorCommand;
use InfyOm\Generator\Commands\Scaffold\ViewsGeneratorCommand;

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
        $this->app->singleton('infyom.publish.api', function ($app) {
            return new APIGeneratorPublisherCommand();
        });

        $this->app->singleton('infyom.api', function ($app) {
            return new APIGeneratorCommand();
        });

        $this->app->singleton('infyom.scaffold', function ($app) {
            return new ScaffoldGeneratorCommand();
        });

        $this->app->singleton('infyom.publish.auth', function ($app) {
            return new AuthPublishCommand();
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

        $this->app->singleton('infyom.publish.tests', function ($app) {
            return new TestCasesPublisherCommand();
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

        $this->commands([
            'infyom.publish.api',
            'infyom.api',
            'infyom.scaffold',
            'infyom.api_scaffold',
            'infyom.publish.auth',
            'infyom.publish.templates',
            'infyom.migration',
            'infyom.model',
            'infyom.repository',
            'infyom.api.controller',
            'infyom.api.requests',
            'infyom.api.tests',
            'infyom.publish.tests',
            'infyom.scaffold.controller',
            'infyom.scaffold.requests',
            'infyom.scaffold.views',
        ]);
    }
}

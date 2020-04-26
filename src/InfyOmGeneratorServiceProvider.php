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
use InfyOm\Generator\Commands\Publish\PublishUserCommand;
use InfyOm\Generator\Commands\RollbackGeneratorCommand;
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
        $timestamp = date('Y_m_d_His', time() + 5);

        $this->publishes([
            __DIR__.'/../install/config/laravel_generator.php' => config_path('infyom/laravel_generator.php'),
            __DIR__.'/../install/database/migrations/2020_04_25_153857_add_timezone_column_for_users.php' => database_path("migrations/{$timestamp}_add_timezone_column_for_users.php"),
            __DIR__.'/../install/app' => app_path(),
        ]);

        $this->publishes([
            __DIR__.'/../install/theme/' => public_path('theme'),
        ], 'public');
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

        $this->app->singleton('infyom.publish.user', function ($app) {
            return new PublishUserCommand();
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
            'infyom.publish.user',
        ]);
    }
}

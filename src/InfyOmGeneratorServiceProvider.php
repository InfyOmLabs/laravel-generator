<?php

namespace InfyOm\Generator;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
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
use InfyOm\Generator\Commands\Publish\PublishTablesCommand;
use InfyOm\Generator\Commands\Publish\PublishUserCommand;
use InfyOm\Generator\Commands\RollbackGeneratorCommand;
use InfyOm\Generator\Commands\Scaffold\ControllerGeneratorCommand;
use InfyOm\Generator\Commands\Scaffold\RequestsGeneratorCommand;
use InfyOm\Generator\Commands\Scaffold\ScaffoldGeneratorCommand;
use InfyOm\Generator\Commands\Scaffold\ViewsGeneratorCommand;
use InfyOm\Generator\Common\FileSystem;
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

class InfyOmGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $configPath = __DIR__.'/../config/laravel_generator.php';
            $this->publishes([
                $configPath => config_path('laravel_generator.php'),
            ], 'laravel-generator-config');

            $this->publishes([
                __DIR__.'/../views' => resource_path('views/vendor/laravel-generator'),
            ], 'laravel-generator-templates');
        }

        $this->registerCommands();
        $this->loadViewsFrom(__DIR__.'/../views', 'laravel-generator');

        View::composer('*', function ($view) {
            $view->with(['config' => app(GeneratorConfig::class)]);
        });

        Blade::directive('tab', function () {
            return '<?php echo infy_tab() ?>';
        });

        Blade::directive('tabs', function ($count) {
            return "<?php echo infy_tabs($count) ?>";
        });

        Blade::directive('nl', function () {
            return '<?php echo infy_nl() ?>';
        });

        Blade::directive('nls', function ($count) {
            return "<?php echo infy_nls($count) ?>";
        });
    }

    private function registerCommands()
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            APIScaffoldGeneratorCommand::class,

            APIGeneratorCommand::class,
            APIControllerGeneratorCommand::class,
            APIRequestsGeneratorCommand::class,
            TestsGeneratorCommand::class,

            MigrationGeneratorCommand::class,
            ModelGeneratorCommand::class,
            RepositoryGeneratorCommand::class,

            GeneratorPublishCommand::class,
            PublishTablesCommand::class,
            PublishUserCommand::class,

            ControllerGeneratorCommand::class,
            RequestsGeneratorCommand::class,
            ScaffoldGeneratorCommand::class,
            ViewsGeneratorCommand::class,

            RollbackGeneratorCommand::class,
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel_generator.php', 'laravel_generator');

        $this->app->singleton(GeneratorConfig::class, function () {
            return new GeneratorConfig();
        });

        $this->app->singleton(FileSystem::class, function () {
            return new FileSystem();
        });

        $this->app->singleton(MigrationGenerator::class);
        $this->app->singleton(ModelGenerator::class);
        $this->app->singleton(RepositoryGenerator::class);

        $this->app->singleton(APIRequestGenerator::class);
        $this->app->singleton(APIControllerGenerator::class);
        $this->app->singleton(APIRoutesGenerator::class);

        $this->app->singleton(RequestGenerator::class);
        $this->app->singleton(ControllerGenerator::class);
        $this->app->singleton(ViewGenerator::class);
        $this->app->singleton(RoutesGenerator::class);
        $this->app->singleton(MenuGenerator::class);

        $this->app->singleton(RepositoryTestGenerator::class);
        $this->app->singleton(APITestGenerator::class);

        $this->app->singleton(FactoryGenerator::class);
        $this->app->singleton(SeederGenerator::class);
    }
}

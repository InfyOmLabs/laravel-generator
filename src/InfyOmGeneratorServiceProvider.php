<?php

namespace InfyOm\Generator;

use Illuminate\Support\ServiceProvider;
use InfyOm\Generator\Common\GeneratorConfig;

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
            ]);
        }
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
    }
}

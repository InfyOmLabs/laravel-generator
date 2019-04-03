<?php

namespace Tests;

use Illuminate\Foundation\Application;
use InfyOm\Generator\InfyOmGeneratorServiceProvider;
use org\bovigo\vfs\vfsStream;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected $withDummy = true;

    protected function getPackageProviders($app)
    {
        return [
            InfyOmGeneratorServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        vfsStream::setup();
        $app['config']->set('infyom.laravel_generator.path.repository', vfsStream::url('root/Repositories/'));
        $app['config']->set('infyom.laravel_generator.path.templates_dir', __DIR__.'/../templates/');
        $app['config']->set('infyom.laravel_generator.path.migration', vfsStream::url('root/Migrations/'));
    }
}
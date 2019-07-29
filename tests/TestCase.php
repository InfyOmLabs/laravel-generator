<?php

namespace Tests;

use Tests\Providers\RouteServiceProvider;
/**
 * Class TestCase
 * @package Tests
 */
class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp() : void
    {
        $aa= __DIR__.'/../databased/factories';
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
        $this->withFactories(__DIR__.'/../database/factories');
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
    }

    protected function getPackageProviders($app)
    {
        return [RouteServiceProvider::class];
    }
}
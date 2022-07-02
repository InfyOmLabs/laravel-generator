<?php

use InfyOm\Generator\Commands\RollbackGeneratorCommand;
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
use Mockery as m;
use function Pest\Laravel\artisan;

afterEach(function () {
    m::close();
});

it('fails with invalid rollback type', function () {
    artisan(RollbackGeneratorCommand::class, ['model' => 'User', 'type' => 'random'])
        ->assertExitCode(1);
});

function mockShouldHaveCalledGenerator(array $shouldHaveCalledGenerators): array
{
    $mockedObjects = [];

    foreach ($shouldHaveCalledGenerators as $generator) {
        $mock = m::mock($generator);

        $mock->shouldReceive('rollback')
            ->once()
            ->andReturn(true);

        app()->singleton($generator, function () use ($mock) {
            return $mock;
        });

        $mockedObjects[] = $mock;
    }

    return $mockedObjects;
}

function mockShouldNotHaveCalledGenerators(array $shouldNotHaveCalledGenerator): array
{
    $mockedObjects = [];

    foreach ($shouldNotHaveCalledGenerator as $generator) {
        $mock = m::mock($generator);

        $mock->shouldNotReceive('rollback');

        app()->singleton($generator, function () use ($mock) {
            return $mock;
        });

        $mockedObjects[] = $mock;
    }

    return $mockedObjects;
}

it('validates that all files are rolled back for api_scaffold', function () {
    $shouldHaveCalledGenerators = [
        MigrationGenerator::class,
        ModelGenerator::class,
        RepositoryGenerator::class,
        APIRequestGenerator::class,
        APIControllerGenerator::class,
        APIRoutesGenerator::class,
        RequestGenerator::class,
        ControllerGenerator::class,
        ViewGenerator::class,
        RoutesGenerator::class,
        MenuGenerator::class,
        FactoryGenerator::class,
        RepositoryTestGenerator::class,
        APITestGenerator::class,
    ];

    mockShouldHaveCalledGenerator($shouldHaveCalledGenerators);

    $shouldNotHaveCalledGenerator = [
        SeederGenerator::class,
    ];

    mockShouldNotHaveCalledGenerators($shouldNotHaveCalledGenerator);

    config()->set('laravel_generator.add_ons.tests', true);

    artisan(RollbackGeneratorCommand::class, ['model' => 'User', 'type' => 'api_scaffold']);
});

it('validates that all files are rolled back for api', function () {
    $shouldHaveCalledGenerators = [
        MigrationGenerator::class,
        ModelGenerator::class,
        APIRequestGenerator::class,
        APIControllerGenerator::class,
        APIRoutesGenerator::class,
        FactoryGenerator::class,
        SeederGenerator::class,
    ];

    mockShouldHaveCalledGenerator($shouldHaveCalledGenerators);

    $shouldNotHaveCalledGenerator = [
        RepositoryGenerator::class,
        RequestGenerator::class,
        ControllerGenerator::class,
        ViewGenerator::class,
        RoutesGenerator::class,
        MenuGenerator::class,
    ];

    mockShouldNotHaveCalledGenerators($shouldNotHaveCalledGenerator);

    config()->set('laravel_generator.options.repository_pattern', false);
    config()->set('laravel_generator.options.factory', true);
    config()->set('laravel_generator.options.seeder', true);

    artisan(RollbackGeneratorCommand::class, ['model' => 'User', 'type' => 'api']);
});

it('validates that all files are rolled back for scaffold', function () {
    $shouldHaveCalledGenerators = [
        MigrationGenerator::class,
        ModelGenerator::class,
        RepositoryGenerator::class,
        RequestGenerator::class,
        ControllerGenerator::class,
        ViewGenerator::class,
        RoutesGenerator::class,
        MenuGenerator::class,
    ];

    mockShouldHaveCalledGenerator($shouldHaveCalledGenerators);

    $shouldNotHaveCalledGenerator = [
        APIRequestGenerator::class,
        APIControllerGenerator::class,
        APIRoutesGenerator::class,
    ];

    mockShouldNotHaveCalledGenerators($shouldNotHaveCalledGenerator);

    config()->set('laravel_generator.add_ons.tests', true);

    artisan(RollbackGeneratorCommand::class, ['model' => 'User', 'type' => 'scaffold']);
});

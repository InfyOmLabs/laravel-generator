<?php

use InfyOm\Generator\Commands\RollbackGeneratorCommand;
use InfyOm\Generator\Generators\API\APIControllerGenerator;
use InfyOm\Generator\Generators\API\APIRequestGenerator;
use InfyOm\Generator\Generators\API\APIRoutesGenerator;
use InfyOm\Generator\Generators\FactoryGenerator;
use InfyOm\Generator\Generators\MigrationGenerator;
use InfyOm\Generator\Generators\ModelGenerator;
use InfyOm\Generator\Generators\RepositoryGenerator;
use InfyOm\Generator\Generators\Scaffold\ControllerGenerator;
use InfyOm\Generator\Generators\Scaffold\MenuGenerator;
use InfyOm\Generator\Generators\Scaffold\RequestGenerator;
use InfyOm\Generator\Generators\Scaffold\RoutesGenerator;
use InfyOm\Generator\Generators\Scaffold\ViewGenerator;
use InfyOm\Generator\Utils\FileUtil;
use function Pest\Laravel\artisan;

beforeEach(function () {
    Mockery::getConfiguration()->setConstantsMap([
        FileUtil::class => [
            'FILE_CREATING' => 1,
            'FILE_CREATED' => 2,
            'FILE_DELETING' => 3,
            'FILE_DELETED' => 4,
        ]
    ]);
});

it('fails with invalid rollback type', function () {
    artisan(RollbackGeneratorCommand::class, ['model' => 'User', 'type' => 'random'])
        ->assertExitCode(1);
});

//it('validates that all files are rolled back', function () {
//
////    $generators = [
////        MigrationGenerator::class,
////        ModelGenerator::class,
////        RepositoryGenerator::class,
////        APIRequestGenerator::class,
////        APIControllerGenerator::class,
////        APIRoutesGenerator::class,
////        RequestGenerator::class,
////        ControllerGenerator::class,
////        ViewGenerator::class,
////        RoutesGenerator::class,
////        MenuGenerator::class,
////    ];
////
////    $mockedObjects = [];
////
////    foreach ($generators as $generator) {
////        $mock = Mockery::mock('overload:'.$generator);
////
////        $mock->shouldReceive('rollback')
////            ->atLeast()
////            ->once()
////            ->andReturn(true);
////
////        $mockedObjects[] = $mock;
////    }
//
//    artisan(RollbackGeneratorCommand::class, ['model' => 'User', 'type' => 'api_scaffold']);
//
//    $mock->expects($this->once())->method('rollback');
//
//    Mockery::close();
//});

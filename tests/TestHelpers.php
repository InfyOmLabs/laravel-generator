<?php

use Illuminate\Console\Command;
use InfyOm\Generator\Common\GeneratorConfig;
use Mockery as m;

function mockShouldHaveCalledGenerateMethod(array $shouldHaveCalledGenerators): array
{
    $mockedObjects = [];

    foreach ($shouldHaveCalledGenerators as $generator) {
        $mock = m::mock($generator);

        $mock->shouldReceive('generate')
            ->once()
            ->andReturn(true);

        app()->singleton($generator, function () use ($mock) {
            return $mock;
        });

        $mockedObjects[] = $mock;
    }

    return $mockedObjects;
}

function mockShouldNotHaveCalledGenerateMethod(array $shouldNotHaveCalledGenerator): array
{
    $mockedObjects = [];

    foreach ($shouldNotHaveCalledGenerator as $generator) {
        $mock = m::mock($generator);

        $mock->shouldNotReceive('generate');

        app()->singleton($generator, function () use ($mock) {
            return $mock;
        });

        $mockedObjects[] = $mock;
    }

    return $mockedObjects;
}

function fakeGeneratorConfig()
{
    $fakeConfig = new GeneratorConfig();
    $command = fakeGeneratorCommand();
    $fakeConfig->setCommand($command);
    $fakeConfig->init();

    app()->singleton(GeneratorConfig::class, function () use ($fakeConfig) {
        return $fakeConfig;
    });

    return $fakeConfig;
}

function fakeGeneratorCommand($options = [])
{
    $mock = m::mock(Command::class);

    $mock->shouldReceive('argument')->withArgs(['model'])->andReturn('FakeModel');
    if (empty($options)) {
        $mock->shouldReceive('option')->withAnyArgs()->andReturn('');
    } else {
        foreach ($options as $option => $value) {
            $mock->shouldReceive('option')->withArgs([$option])->andReturn($value);
        }
    }

    return $mock;
}

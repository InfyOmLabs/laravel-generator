<?php

namespace InfyOm\Generator\DTOs;

class GeneratorNamespaces
{
    public string $app;
    public string $repository;
    public string $model;
    public string $dataTables;
    public string $livewireTables;
    public string $modelExtend;

    public string $seeder;
    public string $factory;

    public string $apiController;
    public string $apiResource;
    public string $apiRequest;

    public string $request;
    public string $requestBase;
    public string $controller;
    public string $baseController;

    public string $apiTests;
    public string $repositoryTests;
    public string $testTraits;
    public string $tests;
}

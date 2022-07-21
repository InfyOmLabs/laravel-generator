<?php

namespace InfyOm\Generator\DTOs;

class GeneratorOptions
{
    public bool $softDelete;
    public bool $saveSchemaFile;
    public bool $localized;
    public bool $repositoryPattern;
    public bool $resources;
    public bool $factory;
    public bool $seeder;
    public bool $swagger;
    public bool $tests;
    public array $excludedFields;
}

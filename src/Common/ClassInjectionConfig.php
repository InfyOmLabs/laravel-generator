<?php

namespace InfyOm\Generator\Common;

/**
 * Class ClassInjectionConfig
 * @package InfyOm\Generator\Common
 */
class ClassInjectionConfig
{
    /**
     * @var array The default classes we'll draw from if we have no overrides
     */
    private static $defaultClasses = [
        'Generators' => [
            'migration' => \InfyOm\Generator\Generators\MigrationGenerator::class,
            'model' => \InfyOm\Generator\Generators\ModelGenerator::class,
            'repository' => \InfyOm\Generator\Generators\RepositoryGenerator::class,
            'repository_test' => \InfyOm\Generator\Generators\RepositoryTestGenerator::class,
            'swagger' => \InfyOm\Generator\Generators\SwaggerGenerator::class,
            'test_trait' => \InfyOm\Generator\Generators\TestTraitGenerator::class
        ]
    ];

    /**
     * @var array A merge of our default classes and those defined in a config file
     */
    private static $classes = [];

    /**
     * This gets the class we need from the config (laravel_generator.php => 'classes')
     * @param $type string class we need in dot notation staring from laravel_generator.php => 'classes', e.g. 'Generators.model'
     * @return mixed
     * @throws \Exception
     */
    public static function getClassByConfigPath($type)
    {
        if(empty(static::$classes)){
            static::populateClasses();
        }

        $class = array_get(static::$classes, $type);
        if(empty($class) || !class_exists($class)){
            throw new \Exception('Classname for infyom.laravel_generator.classes.'.$type.' does not exist: '.$class);
        }
        return $class;
    }

    /**
     * Creates a class with config path + arguments
     * @param $type string
     * @param $arguments array
     * @return object
     * @throws \ReflectionException
     * @throws \Exception
     */
    public static function createClassByConfigPath($type, $arguments = [])
    {
        $reflection = new \ReflectionClass(static::getClassByConfigPath($type));
        return $reflection->newInstanceArgs($arguments);
    }

    /**
     * Populates our classes array by merging the config with the defaults in this static class
     */
    private static function populateClasses(): void
    {
        static::$classes = array_replace_recursive(static::$defaultClasses, config('infyom.laravel_generator.classes', []));
    }
}

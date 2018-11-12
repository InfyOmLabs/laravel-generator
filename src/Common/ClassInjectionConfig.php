<?php

namespace InfyOm\Generator\Common;


/**
 * Class ClassInjectionConfig
 * @package InfyOm\Generator\Common
 *
 * This class handles all class injections for this library, and provides a mechanism for developers extend & inject
 * their own classes into this library.  This class is designed to make this code base much more configurable.
 */
class ClassInjectionConfig
{
    /**
     * @var array The default classes we'll draw from if we have no overrides
     */
    private static $defaultClasses = [
        'Common' => [
            'command_data' => \InfyOm\Generator\Common\CommandData::class,
            'generator_config' => \InfyOm\Generator\Common\GeneratorConfig::class,
            'generator_field' => \InfyOm\Generator\Common\GeneratorField::class,
            'generator_field_relation' => \InfyOm\Generator\Common\GeneratorFieldRelation::class,
        ],
        'Generators' => [
            'API' => [
                'api_controller' => \InfyOm\Generator\Generators\API\APIControllerGenerator::class,
                'api_request' => \InfyOm\Generator\Generators\API\APIRequestGenerator::class,
                'api_routes' => \InfyOm\Generator\Generators\API\APIRoutesGenerator::class,
                'api_test' => \InfyOm\Generator\Generators\API\APITestGenerator::class,
            ],
            'Scaffold' => [
                'controller' =>  \InfyOm\Generator\Generators\Scaffold\ControllerGenerator::class,
                'menu' =>  \InfyOm\Generator\Generators\Scaffold\MenuGenerator::class,
                'request' =>  \InfyOm\Generator\Generators\Scaffold\RequestGenerator::class,
                'routes' =>  \InfyOm\Generator\Generators\Scaffold\RoutesGenerator::class,
                'view' =>  \InfyOm\Generator\Generators\Scaffold\ViewGenerator::class,
            ],
            'VueJs' => [
                'api_request' => \InfyOm\Generator\Generators\VueJs\APIRequestGenerator::class,
                'controller' => \InfyOm\Generator\Generators\VueJs\ControllerGenerator::class,
                'model_js_config' => \InfyOm\Generator\Generators\VueJs\ModelJsConfigGenerator::class,
                'routes' => \InfyOm\Generator\Generators\Vuejs\RoutesGenerator::class,
                'test' => \InfyOm\Generator\Generators\API\TestGenerator::class,
                'view' => \InfyOm\Generator\Generators\VueJs\ViewGenerator::class,

            ],
            'migration' => \InfyOm\Generator\Generators\MigrationGenerator::class,
            'model' => \InfyOm\Generator\Generators\ModelGenerator::class,
            'repository' => \InfyOm\Generator\Generators\RepositoryGenerator::class,
            'repository_test' => \InfyOm\Generator\Generators\RepositoryTestGenerator::class,
            'swagger' => \InfyOm\Generator\Generators\SwaggerGenerator::class,
            'test_trait' => \InfyOm\Generator\Generators\TestTraitGenerator::class
        ],
        'Utils' => [
            'table_field_generator' => \InfyOm\Generator\Utils\TableFieldsGenerator::class
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
            throw new \RuntimeException('Classname for infyom.laravel_generator.classes.'.$type.' does not exist: '.$class);
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

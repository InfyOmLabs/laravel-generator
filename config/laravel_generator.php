<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Paths
    |--------------------------------------------------------------------------
    |
    */

    'path' => [

        'migration'         => base_path('database/migrations/'),

        'model'             => app_path('Models/'),

        'datatables'        => app_path('DataTables/'),

        'repository'        => app_path('Repositories/'),

        'routes'            => base_path('routes/web.php'),

        'api_routes'        => base_path('routes/api.php'),

        'request'           => app_path('Http/Requests/'),

        'api_request'       => app_path('Http/Requests/API/'),

        'controller'        => app_path('Http/Controllers/'),

        'api_controller'    => app_path('Http/Controllers/API/'),

        'test_trait'        => base_path('tests/traits/'),

        'repository_test'   => base_path('tests/'),

        'api_test'          => base_path('tests/'),

        'views'             => base_path('resources/views/'),

        'schema_files'      => base_path('resources/model_schemas/'),

        'templates_dir'     => base_path('resources/infyom/infyom-generator-templates/'),

        'modelJs'           => base_path('resources/assets/js/models/'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Namespaces
    |--------------------------------------------------------------------------
    |
    */

    'namespace' => [

        'model'             => 'App\Models',

        'datatables'        => 'App\DataTables',

        'repository'        => 'App\Repositories',

        'controller'        => 'App\Http\Controllers',

        'api_controller'    => 'App\Http\Controllers\API',

        'request'           => 'App\Http\Requests',

        'api_request'       => 'App\Http\Requests\API',
    ],

    /*
    |--------------------------------------------------------------------------
    | Templates
    |--------------------------------------------------------------------------
    |
    */

    'templates'         => 'adminlte-templates',

    /*
    |--------------------------------------------------------------------------
    | Model extend class
    |--------------------------------------------------------------------------
    |
    */

    'model_extend_class' => 'Eloquent',

    /*
    |--------------------------------------------------------------------------
    | API routes prefix & version
    |--------------------------------------------------------------------------
    |
    */

    'api_prefix'  => 'api',

    'api_version' => 'v1',

    /*
    |--------------------------------------------------------------------------
    | Options
    |--------------------------------------------------------------------------
    |
    */

    'options' => [

        'softDelete' => true,

        'tables_searchable_default' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Prefixes
    |--------------------------------------------------------------------------
    |
    */

    'prefixes' => [

        'route' => '',  // using admin will create route('admin.?.index') type routes

        'path' => '',

        'view' => '',  // using backend will create return view('backend.?.index') type the backend views directory

        'public' => '',
    ],

    /*
    |--------------------------------------------------------------------------
    | Add-Ons
    |--------------------------------------------------------------------------
    |
    */

    'add_on' => [

        'swagger'       => false,

        'tests'         => true,

        'datatables'    => false,

        'menu'          => [

            'enabled'       => true,

            'menu_file'     => 'layouts/menu.blade.php',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Timestamp Fields
    |--------------------------------------------------------------------------
    |
    */

    'timestamps' => [

        'enabled'       => true,

        'created_at'    => 'created_at',

        'updated_at'    => 'updated_at',

        'deleted_at'    => 'deleted_at',
    ],

    /*
    |--------------------------------------------------------------------------
    | Save model files to `App/Models` when use `--prefix`. see #208
    |--------------------------------------------------------------------------
    |
    */
    'ignore_model_prefix' => false,

    'classes' => [
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
        ]
    ]
];

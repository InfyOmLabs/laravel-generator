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

        'repository'        => app_path('Repositories/'),

        'routes'            => app_path('Http/routes.php'),

        'api_routes'        => app_path('Http/api_routes.php'),

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
    ],

    /*
    |--------------------------------------------------------------------------
    | Namespaces
    |--------------------------------------------------------------------------
    |
    */

    'namespace' => [

        'model'             => 'App\Models',

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

    'templates'         => 'core-templates',

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
    ],

    /*
    |--------------------------------------------------------------------------
    | Add-Ons
    |--------------------------------------------------------------------------
    |
    */

    'add_on' => [

        'swagger'   => true,

        'tests'     => true,
    ],
];

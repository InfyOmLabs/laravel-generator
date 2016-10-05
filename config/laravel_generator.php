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

];

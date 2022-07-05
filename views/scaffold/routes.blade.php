Route::resource('{{ $config->modelNames->dashedPlural }}', {{ $config->namespaces->controller }}\{{ $config->modelNames->name }}Controller::class);

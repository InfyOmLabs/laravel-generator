<?php

namespace InfyOm\Generator\Commands\Publish;

class PublishUserCommand extends PublishBaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'infyom.publish:user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishes Users CRUD file';

    public function handle()
    {
        $this->copyViews();
        $this->updateRoutes();
        $this->updateMenu();
        $this->publishUserController();
        if (config('laravel_generator.options.repository_pattern')) {
            $this->publishUserRepository();
        }
        $this->publishCreateUserRequest();
        $this->publishUpdateUserRequest();
    }

    private function copyViews()
    {
        $viewsPath = config('laravel_generator.path.views', resource_path('views/'));
        $templateType = config('laravel_generator.templates', 'adminlte-templates');

        $this->createDirectories($viewsPath.'users');

        $files = $this->getViews();

        foreach ($files as $templateView => $destinationView) {
            $content = view($templateType.'::'.$templateView);
            $destinationFile = $viewsPath.$destinationView;
            g_filesystem()->createFile($destinationFile, $content);
        }
    }

    private function createDirectories($dir)
    {
        g_filesystem()->createDirectoryIfNotExist($dir);
    }

    private function getViews(): array
    {
        return [
            'users.create'      => 'users/create.blade.php',
            'users.edit'        => 'users/edit.blade.php',
            'users.fields'      => 'users/fields.blade.php',
            'users.index'       => 'users/index.blade.php',
            'users.show'        => 'users/show.blade.php',
            'users.show_fields' => 'users/show_fields.blade.php',
            'users.table'       => 'users/table.blade.php',
        ];
    }

    private function updateRoutes()
    {
        $path = config('laravel_generator.path.routes', base_path('routes/web.php'));

        $routeContents = g_filesystem()->getFile($path);
        $controllerNamespace = config('laravel_generator.namespace.controller');
        $routeContents .= infy_nls(2)."Route::resource('users', '$controllerNamespace\UserController')->middleware('auth');";

        g_filesystem()->createFile($path, $routeContents);
        $this->comment("\nUser route added");
    }

    private function updateMenu()
    {
        $viewsPath = config('laravel_generator.path.views', resource_path('views/'));
        $templateType = config('laravel_generator.templates', 'adminlte-templates');
        $path = $viewsPath.'layouts/menu.blade.php';
        $menuContents = g_filesystem()->getFile($path);
        $usersMenuContent = view($templateType.'::templates.users.menu')->render();
        $menuContents .= infy_nl().$usersMenuContent;

        g_filesystem()->createFile($path, $menuContents);
        $this->comment("\nUser Menu added");
    }

    private function publishUserController()
    {
        $name = 'user_controller';

        if (!config('laravel_generator.options.repository_pattern')) {
            $name = 'user_controller_without_repository';
        }

        $controllerPath = config('laravel_generator.path.controller', app_path('Http/Controllers/'));
        $controllerPath .= 'UserController.php';

        $controllerContents = view('laravel-generator::scaffold.user.'.$name)->render();

        g_filesystem()->createFile($controllerPath, $controllerContents);

        $this->info('UserController created');
    }

    private function publishUserRepository()
    {
        $repositoryPath = config('laravel_generator.path.repository', app_path('Repositories/'));

        $fileName = 'UserRepository.php';

        g_filesystem()->createDirectoryIfNotExist($repositoryPath);

        if (file_exists($repositoryPath.$fileName) && !$this->confirmOverwrite($fileName)) {
            return;
        }

        $templateData = view('laravel-generator::scaffold.user.user_repository')->render();
        g_filesystem()->createFile($repositoryPath.$fileName, $templateData);

        $this->info('UserRepository created');
    }

    private function publishCreateUserRequest()
    {
        $requestPath = config('laravel_generator.path.request', app_path('Http/Requests/'));

        $fileName = 'CreateUserRequest.php';

        g_filesystem()->createDirectoryIfNotExist($requestPath);

        if (file_exists($requestPath.$fileName) && !$this->confirmOverwrite($fileName)) {
            return;
        }

        $templateData = view('laravel-generator::scaffold.user.create_user_request')->render();
        g_filesystem()->createFile($requestPath.$fileName, $templateData);

        $this->info('CreateUserRequest created');
    }

    private function publishUpdateUserRequest()
    {
        $requestPath = config('laravel_generator.path.request', app_path('Http/Requests/'));

        $fileName = 'UpdateUserRequest.php';
        if (file_exists($requestPath.$fileName) && !$this->confirmOverwrite($fileName)) {
            return;
        }

        $templateData = view('laravel-generator::scaffold.user.update_user_request')->render();
        g_filesystem()->createFile($requestPath.$fileName, $templateData);

        $this->info('UpdateUserRequest created');
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return [];
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }
}

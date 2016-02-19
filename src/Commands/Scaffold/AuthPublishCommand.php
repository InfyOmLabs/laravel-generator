<?php

namespace InfyOm\Generator\Commands\Scaffold;

use InfyOm\Generator\Commands\PublishBaseCommand;
use InfyOm\Generator\Utils\FileUtil;
use InfyOm\Generator\Utils\TemplateUtil;

class AuthPublishCommand extends PublishBaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'infyom.publish:auth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishes auth files';

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $version = $this->getApplication()->getVersion();

        if (str_contains($version, '5.1')) {
            $this->copyView();
            $this->updateRoutes();
        } else {
            $this->error('Publish Auth is only available for Laravel version 5.1');
            $this->comment('If you are running Laravel version 5.2.*, then try php artisan make:auth');
        }
    }

    private function copyView()
    {
        $viewsPath = config('infyom.laravel_generator.path.views', base_path('resources/views/'));
        $templateType = config('infyom.laravel_generator.path.templates', 'core-templates');

        FileUtil::createDirectoryIfNotExist($viewsPath.'layouts');
        FileUtil::createDirectoryIfNotExist($viewsPath.'auth');
        FileUtil::createDirectoryIfNotExist($viewsPath.'auth/passwords');
        FileUtil::createDirectoryIfNotExist($viewsPath.'emails');

        $files = [
            'layouts/app.stub'     => 'layouts/app.blade.php',
            'auth/login.stub'      => 'auth/login.blade.php',
            'auth/register.stub'   => 'auth/register.blade.php',
            'auth/password.stub'   => 'auth/password.blade.php',
            'auth/reset.stub'      => 'auth/reset.blade.php',
            'emails/password.stub' => 'emails/password.blade.php',
        ];

        foreach ($files as $stub => $blade) {
            $sourceFile = base_path('vendor/infyomlabs/'.$templateType.'/templates/scaffold/'.$stub);
            $destinationFile = $viewsPath.$blade;
            $this->publishFile($sourceFile, $destinationFile, $blade);
        }
    }

    private function updateRoutes()
    {
        $path = config('infyom.laravel_generator.path.routes', app_path('Http/routes.php'));
        $routeContents = file_get_contents($path);

        $routesTemplate = TemplateUtil::getTemplate('routes.auth', 'laravel-generator');
        $routeContents .= "\n\n".$routesTemplate;

        file_put_contents($path, $routeContents);
        $this->comment("\nRoutes added");
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
     * Publishes api_routes.php.
     */
    public function publishAPIRoutes()
    {
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

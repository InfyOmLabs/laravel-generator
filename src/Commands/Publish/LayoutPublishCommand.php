<?php

namespace InfyOm\Generator\Commands\Publish;

use InfyOm\Generator\Utils\FileUtil;
use InfyOm\Generator\Utils\TemplateUtil;

class LayoutPublishCommand extends PublishBaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'infyom.publish:layout';

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
        $this->copyView();
        $this->updateRoutes();
    }

    private function copyView()
    {
        $viewsPath = config('infyom.laravel_generator.path.views', base_path('resources/views/'));
        $templateType = config('infyom.laravel_generator.templates', 'core-templates');

        FileUtil::createDirectoryIfNotExist($viewsPath.'layouts');
        FileUtil::createDirectoryIfNotExist($viewsPath.'auth');
        FileUtil::createDirectoryIfNotExist($viewsPath.'auth/passwords');
        FileUtil::createDirectoryIfNotExist($viewsPath.'emails');

        $files = [
            'layouts/app.stub'     => 'layouts/app.blade.php',
            'layouts/sidebar.stub' => 'layouts/sidebar.blade.php',
            'layouts/menu.stub'    => 'layouts/menu.blade.php',
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

<?php

namespace InfyOm\Generator\Commands\Publish;

use InfyOm\Generator\Utils\FileUtil;

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
        $this->publishHomeController();
    }

    private function copyView()
    {
        $viewsPath = config('infyom.laravel_generator.path.views', base_path('resources/views/'));
        $templateType = config('infyom.laravel_generator.templates', 'adminlte-templates');

        $this->createDirectories($viewsPath);

        $files = $this->getViews();

        foreach ($files as $stub => $blade) {
            $sourceFile = get_template_file_path('scaffold/'.$stub, $templateType);
            $destinationFile = $viewsPath.$blade;
            $this->publishFile($sourceFile, $destinationFile, $blade);
        }
    }

    private function createDirectories($viewsPath)
    {
        FileUtil::createDirectoryIfNotExist($viewsPath.'layouts');
        FileUtil::createDirectoryIfNotExist($viewsPath.'auth');

        FileUtil::createDirectoryIfNotExist($viewsPath.'auth/passwords');
        FileUtil::createDirectoryIfNotExist($viewsPath.'auth/emails');
    }

    private function getViews()
    {
        return [
            'layouts/app'               => 'layouts/app.blade.php',
            'layouts/sidebar'           => 'layouts/sidebar.blade.php',
            'layouts/datatables_css'    => 'layouts/datatables_css.blade.php',
            'layouts/datatables_js'     => 'layouts/datatables_js.blade.php',
            'layouts/menu'              => 'layouts/menu.blade.php',
            'layouts/home'              => 'home.blade.php',
            'auth/login'                => 'auth/login.blade.php',
            'auth/register'             => 'auth/register.blade.php',
            'auth/email'                => 'auth/passwords/email.blade.php',
            'auth/reset'                => 'auth/passwords/reset.blade.php',
            'emails/password'           => 'auth/emails/password.blade.php',
        ];
    }

    private function updateRoutes()
    {
        $path = config('infyom.laravel_generator.path.routes', app_path('routes/web.php'));

        $prompt = 'Existing routes web.php file detected. Should we add standard auth routes? (y|N) :';
        if (file_exists($path) && !$this->confirmOverwrite($path, $prompt)) {
            return;
        }

        $routeContents = file_get_contents($path);

        $routesTemplate = get_template('routes.auth', 'laravel-generator');

        $routeContents .= "\n\n".$routesTemplate;

        file_put_contents($path, $routeContents);
        $this->comment("\nRoutes added");
    }

    private function publishHomeController()
    {
        $templateData = get_template('home_controller', 'laravel-generator');

        $templateData = $this->fillTemplate($templateData);

        $controllerPath = config('infyom.laravel_generator.path.controller', app_path('Http/Controllers/'));

        $fileName = 'HomeController.php';

        if (file_exists($controllerPath.$fileName) && !$this->confirmOverwrite($fileName)) {
            return;
        }

        FileUtil::createFile($controllerPath, $fileName, $templateData);

        $this->info('HomeController created');
    }

    /**
     * Replaces dynamic variables of template.
     *
     * @param string $templateData
     *
     * @return string
     */
    private function fillTemplate($templateData)
    {
        $templateData = str_replace(
            '$NAMESPACE_CONTROLLER$',
            config('infyom.laravel_generator.namespace.controller'), $templateData
        );

        $templateData = str_replace(
            '$NAMESPACE_REQUEST$',
            config('infyom.laravel_generator.namespace.request'), $templateData
        );

        return $templateData;
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

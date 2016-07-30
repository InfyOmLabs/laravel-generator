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
     * Laravel Application version.
     *
     * @var string
     */
    protected $laravelVersion;

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $version = $this->getApplication()->getVersion();
        if (str_contains($version, '5.1')) {
            $this->laravelVersion = '5.1';
        } else {
            $this->laravelVersion = '5.2';
        }
        $this->copyView();
        $this->updateRoutes();
        $this->publishHomeController();
    }

    private function getViews()
    {
        if ($this->laravelVersion == '5.1') {
            return $this->getLaravel51Views();
        } else {
            return $this->getLaravel52Views();
        }
    }

    private function createDirectories($viewsPath)
    {
        FileUtil::createDirectoryIfNotExist($viewsPath.'layouts');
        FileUtil::createDirectoryIfNotExist($viewsPath.'auth');

        if ($this->laravelVersion == '5.1') {
            FileUtil::createDirectoryIfNotExist($viewsPath.'emails');
        } else {
            FileUtil::createDirectoryIfNotExist($viewsPath.'auth/passwords');
            FileUtil::createDirectoryIfNotExist($viewsPath.'auth/emails');
        }
    }

    private function getLaravel51Views()
    {
        return [
            'layouts/app'     => 'layouts/app.blade.php',
            'layouts/sidebar' => 'layouts/sidebar.blade.php',
            'layouts/menu'    => 'layouts/menu.blade.php',
            'layouts/home'    => 'home.blade.php',
            'auth/login'      => 'auth/login.blade.php',
            'auth/register'   => 'auth/register.blade.php',
            'auth/email'      => 'auth/password.blade.php',
            'auth/reset'      => 'auth/reset.blade.php',
            'emails/password' => 'emails/password.blade.php',
        ];
    }

    private function getLaravel52Views()
    {
        return [
            'layouts/app'     => 'layouts/app.blade.php',
            'layouts/sidebar' => 'layouts/sidebar.blade.php',
            'layouts/menu'    => 'layouts/menu.blade.php',
            'layouts/home'    => 'home.blade.php',
            'auth/login'      => 'auth/login.blade.php',
            'auth/register'   => 'auth/register.blade.php',
            'auth/email'      => 'auth/passwords/email.blade.php',
            'auth/reset'      => 'auth/passwords/reset.blade.php',
            'emails/password' => 'auth/emails/password.blade.php',
        ];
    }

    private function copyView()
    {
        $viewsPath = config('infyom.laravel_generator.path.views', base_path('resources/views/'));
        $templateType = config('infyom.laravel_generator.templates', 'core-templates');

        $this->createDirectories($viewsPath);

        $files = $this->getViews();

        foreach ($files as $stub => $blade) {
            $sourceFile = TemplateUtil::getTemplateFilePath('scaffold/'.$stub, $templateType);
            $destinationFile = $viewsPath.$blade;
            $this->publishFile($sourceFile, $destinationFile, $blade);
        }
    }

    private function updateRoutes()
    {
        $path = config('infyom.laravel_generator.path.routes', app_path('Http/routes.php'));

        $prompt = 'Existing routes.php file detected. Should we add standard routes? (y|N) :';
        if (file_exists($path) && !$this->confirmOverwrite($path, $prompt)) {
            return;
        }

        $routeContents = file_get_contents($path);

        $routesTemplate = TemplateUtil::getTemplate('routes.auth', 'laravel-generator');
        if ($this->laravelVersion == '5.1') {
            $routesTemplate = str_replace('$LOGOUT_METHOD$', 'getLogout', $routesTemplate);
        } else {
            $routesTemplate = str_replace('$LOGOUT_METHOD$', 'logout', $routesTemplate);
        }

        $routeContents .= "\n\n".$routesTemplate;

        file_put_contents($path, $routeContents);
        $this->comment("\nRoutes added");
    }

    private function publishHomeController()
    {
        $templateData = TemplateUtil::getTemplate('home_controller', 'laravel-generator');

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

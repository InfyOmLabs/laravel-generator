<?php

namespace InfyOm\Generator\Commands\Publish;

use Illuminate\Support\Str;
use InfyOm\Generator\Utils\FileUtil;
use Symfony\Component\Console\Input\InputOption;

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
        $this->publishHomeController();
    }

    private function copyView()
    {
        $viewsPath = config('infyom.laravel_generator.path.views', resource_path('views/'));
        $templateType = config('infyom.laravel_generator.templates', 'adminlte-templates');

        $this->createDirectories($viewsPath);

        if ($this->option('localized')) {
            $files = $this->getLocaleViews();
        } else {
            $files = $this->getViews();
        }

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
        $views = [
            'layouts/app'               => 'layouts/app.blade.php',
            'layouts/sidebar'           => 'layouts/sidebar.blade.php',
            'layouts/datatables_css'    => 'layouts/datatables_css.blade.php',
            'layouts/datatables_js'     => 'layouts/datatables_js.blade.php',
            'layouts/menu'              => 'layouts/menu.blade.php',
            'layouts/home'              => 'home.blade.php',
            'auth/login'                => 'auth/login.blade.php',
            'auth/register'             => 'auth/register.blade.php',
            'auth/passwords/confirm'    => 'auth/passwords/confirm.blade.php',
            'auth/passwords/email'      => 'auth/passwords/email.blade.php',
            'auth/passwords/reset'      => 'auth/passwords/reset.blade.php',
            'auth/emails/password'      => 'auth/emails/password.blade.php',
        ];

        $version = $this->getApplication()->getVersion();
        if (Str::contains($version, '6.')) {
            $verifyView = [
                'auth/verify_6' => 'auth/verify.blade.php',
            ];
        } else {
            $verifyView = [
                'auth/verify' => 'auth/verify.blade.php',
            ];
        }

        $views = array_merge($views, $verifyView);

        return $views;
    }

    private function getLocaleViews()
    {
        return [
            'layouts/app_locale'           => 'layouts/app.blade.php',
            'layouts/sidebar_locale'       => 'layouts/sidebar.blade.php',
            'layouts/datatables_css'       => 'layouts/datatables_css.blade.php',
            'layouts/datatables_js'        => 'layouts/datatables_js.blade.php',
            'layouts/menu'                 => 'layouts/menu.blade.php',
            'layouts/home'                 => 'home.blade.php',
            'auth/login_locale'            => 'auth/login.blade.php',
            'auth/register_locale'         => 'auth/register.blade.php',
            'auth/passwords/email_locale'  => 'auth/passwords/email.blade.php',
            'auth/passwords/reset_locale'  => 'auth/passwords/reset.blade.php',
            'auth/emails/password_locale'  => 'auth/emails/password.blade.php',
        ];
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
            config('infyom.laravel_generator.namespace.controller'),
            $templateData
        );

        $templateData = str_replace(
            '$NAMESPACE_REQUEST$',
            config('infyom.laravel_generator.namespace.request'),
            $templateData
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
        return [
            ['localized', null, InputOption::VALUE_NONE, 'Localize files.'],
        ];
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

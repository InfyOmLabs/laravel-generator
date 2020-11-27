<?php

namespace InfyOm\Generator\Commands\Publish;

use Illuminate\Support\Str;
use InfyOm\Generator\Utils\FileUtil;
use Symfony\Component\Console\Input\InputOption;

class PublishStislaLayout extends PublishBaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'infyom.publish:stisla';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishes Stisla Theme layouts';

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
        $templateType = config('infyom.laravel_generator.templates', 'stisla-templates');

        $this->createDirectories($viewsPath);

        $files = $this->getViews();

        foreach ($files as $stub => $blade) {
            $sourceFile = get_template_file_path('scaffold/'.$stub, $templateType);
            $destinationFile = $viewsPath.$blade;
            $this->publishFile($sourceFile, $destinationFile, $blade);
        }


        // Publish Package.json and webpack mix
        $rootViews = [
            'vendors/package' => 'package.json',
            'vendors/webpack' => 'webpack.mix.js',
        ];

        foreach ($rootViews as $stub => $blade) {
            $sourceFile = get_template_file_path('scaffold/'.$stub, $templateType);
            $destinationFile = base_path($blade);
            $this->publishFile($sourceFile, $destinationFile, $blade);
        }

        $assets = [
            'vendors/public/css'       => 'public/css',
            'vendors/public/js'        => 'public/js',
            'vendors/public/img'       => 'public/img',
            'vendors/public/web'       => 'public/web',
            'vendors/resources/assets' => 'resources/assets',
            'vendors/resources/js'     => 'resources/js',
            'vendors/seeds'            => 'database/seeders',
        ];

        foreach ($assets as $asset => $destination) {
            $sourceFile = get_template_directory('scaffold/'.$asset, $templateType);
            $destinationFile = base_path($destination);
            $this->publishDirectory($sourceFile, $destinationFile, $destination);
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
            'layouts/app'             => 'layouts/app.blade.php',
            'layouts/sidebar'         => 'layouts/sidebar.blade.php',
            'layouts/menu'            => 'layouts/menu.blade.php',
            'layouts/footer'          => 'layouts/footer.blade.php',
            'auth/login'              => 'auth/login.blade.php',
            'auth/register'           => 'auth/register.blade.php',
            'auth/reset'              => 'auth/passwords/reset.blade.php',
            'emails/password'         => 'auth/emails/password.blade.php',
            'auth/auth_app'           => 'layouts/auth_app.blade.php',
            'auth/forgot-password'    => 'auth/forgot-password.blade.php',
            'auth/reset-password'     => 'auth/reset-password.blade.php',
            'layouts/header'          => 'layouts/header.blade.php',
            'profile/change_password' => 'profile/change_password.blade.php',
            'profile/edit_profile'    => 'profile/edit_profile.blade.php',
            'auth/verify'             => 'auth/verify.blade.php',
        ];

        return $views;
    }

    private function publishHomeController()
    {
        $templateData = get_template('home_controller', 'laravel-generator');

        $templateData = $this->fillTemplate($templateData);

        $controllerPath = config('infyom.laravel_generator.path.controller', app_path('Http/Controllers/'));

        $fileName = 'HomeController.php';

        if (file_exists($controllerPath.$fileName) && ! $this->confirmOverwrite($fileName)) {
            return;
        }

        FileUtil::createFile($controllerPath, $fileName, $templateData);

        $this->info('HomeController created');
    }

    /**
     * Replaces dynamic variables of template.
     *
     * @param  string  $templateData
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

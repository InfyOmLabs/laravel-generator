<?php

namespace InfyOm\Generator\Commands\Publish;

use InfyOm\Generator\Utils\FileUtil;

class VueJsLayoutPublishCommand extends PublishBaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'infyom.publish:vuejs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish custom files and directories for VueJs Crud Version';

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

    private function copyView()
    {
        $viewsPath = config('infyom.laravel_generator.path.views', base_path('resources/views/'));
        $resourcesPath = config('infyom.laravel_generator.path.resourcesPath', base_path('resources/'));
        $vendorPath = $resourcesPath.'assets/vendor/';
        $assetsJsPath = config('infyom.laravel_generator.path.assetsJsPath', base_path('resources/assets/js/'));
        $assetsCssPath = config('infyom.laravel_generator.path.assetsCssPath', base_path('resources/assets/css/'));
        $templateType = config('infyom.laravel_generator.templates', 'core-templates');
        $requestPath = config('infyom.laravel_generator.path.api_request', base_path('app/Http/Requests/'));

        $this->createDirectories($viewsPath);
        $this->createVueJsDirectories($viewsPath, $resourcesPath);

        $files = $this->getViews();
        $filesJs = $this->getVueJsCrudFile();
        $filesCss = $this->getVueJsCssIndexFile();
        $filesVendor = $this->getVendorFiles();
        $vueLayoutFiles = $this->getVueJsViews();
        $baseRequestCustomFiles = $this->getRequestBaseCustomFile();

        foreach ($files as $stub => $blade) {
            $sourceFile = base_path('vendor/infyomlabs/'.$templateType.'/templates/'.$stub);
            $destinationFile = $viewsPath.$blade;
            $this->publishFile($sourceFile, $destinationFile, $blade);
        }

        foreach ($vueLayoutFiles as $stub => $blade) {
            $sourceFile = base_path('vendor/infyomlabs/'.$templateType.'/templates/vuejs/'.$stub);
            $destinationFile = $viewsPath.$blade;
            $this->publishFile($sourceFile, $destinationFile, $blade);
        }

        foreach ($baseRequestCustomFiles as $stub => $php) {
            $sourceFile = base_path('vendor/infyomlabs/laravel-generator/templates/'.$stub);
            $destinationFile = $requestPath.$php;
            $this->publishFile($sourceFile, $destinationFile, $php);
        }

        foreach ($filesJs as $stub => $blade) {
            $sourceFile = base_path('vendor/infyomlabs/laravel-generator/templates/'.$stub);
            $destinationFile = $assetsJsPath.$blade;
            $this->publishFile($sourceFile, $destinationFile, $blade);
        }

        foreach ($filesCss as $stub => $blade) {
            $sourceFile = base_path('vendor/infyomlabs/laravel-generator/templates/'.$stub);
            $destinationFile = $assetsCssPath.$blade;
            $this->publishFile($sourceFile, $destinationFile, $blade);
        }

        foreach ($filesVendor as $stub => $blade) {
            $sourceFile = base_path('vendor/infyomlabs/laravel-generator/templates/'.$stub);
            $destinationFile = $vendorPath.$blade;
            $this->publishFile($sourceFile, $destinationFile, $blade);
        }

        $sourceFile = base_path('vendor/infyomlabs/laravel-generator/templates/vuejs/js/gulpfile.js');
        $destinationFile = base_path().'/gulpfile.js';
        $this->publishFile($sourceFile, $destinationFile, 'gulpfile.js');

        $sourceFile = base_path('vendor/infyomlabs/laravel-generator/templates/vuejs/js/package.json');
        $destinationFile = base_path().'/package.json';
        $this->publishFile($sourceFile, $destinationFile, 'package.json');
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

    private function createVueJsDirectories($viewsPath, $resourcesPath)
    {
        FileUtil::createDirectoryIfNotExist($viewsPath.'layouts/modal');
        FileUtil::createDirectoryIfNotExist($resourcesPath.'assets/js');
        FileUtil::createDirectoryIfNotExist($resourcesPath.'assets/css');
        FileUtil::createDirectoryIfNotExist($resourcesPath.'assets/vendor/vue-editable');
        FileUtil::createDirectoryIfNotExist($resourcesPath.'assets/vendor/vue-table/components');
        FileUtil::createDirectoryIfNotExist($resourcesPath.'assets/vendor/vue-strap');
        FileUtil::createDirectoryIfNotExist(base_path('app/Http/Requests/API'));
    }

    private function getViews()
    {
        if ($this->laravelVersion == '5.1') {
            return $this->getLaravel51Views();
        } else {
            return $this->getLaravel52Views();
        }
    }

    private function getLaravel51Views()
    {
        return [
            'vuejs/layouts/app.stub'        => 'layouts/app.blade.php',
            'scaffold/layouts/sidebar.stub' => 'layouts/sidebar.blade.php',
            'scaffold/layouts/menu.stub'    => 'layouts/menu.blade.php',
            'scaffold/layouts/home.stub'    => 'home.blade.php',
            'scaffold/auth/login.stub'      => 'auth/login.blade.php',
            'scaffold/auth/register.stub'   => 'auth/register.blade.php',
            'scaffold/auth/email.stub'      => 'auth/password.blade.php',
            'scaffold/auth/reset.stub'      => 'auth/reset.blade.php',
            'scaffold/emails/password.stub' => 'emails/password.blade.php',
        ];
    }

    private function getLaravel52Views()
    {
        return [
            'vuejs/layouts/app.stub'        => 'layouts/app.blade.php',
            'scaffold/layouts/sidebar.stub' => 'layouts/sidebar.blade.php',
            'scaffold/layouts/menu.stub'    => 'layouts/menu.blade.php',
            'scaffold/layouts/home.stub'    => 'home.blade.php',
            'scaffold/auth/login.stub'      => 'auth/login.blade.php',
            'scaffold/auth/register.stub'   => 'auth/register.blade.php',
            'scaffold/auth/email.stub'      => 'auth/passwords/email.blade.php',
            'scaffold/auth/reset.stub'      => 'auth/passwords/reset.blade.php',
            'scaffold/emails/password.stub' => 'auth/emails/password.blade.php',
        ];
    }

    private function getVueJsCrudFile()
    {
        return ['vuejs/js/crud.js' => 'crud.js'];
    }

    private function getVueJsCssIndexFile()
    {
        return ['vuejs/css/vue-styles.css' => 'vue-styles.css'];
    }

    private function getVendorFiles()
    {
        return [
            'vuejs/vendor/vue-editable/vue-editable.js'                      => 'vue-editable/vue-editable.js',
            'vuejs/vendor/vue-strap/vue-strap.min.js'                        => 'vue-strap/vue-strap.min.js',
            'vuejs/vendor/vue-table/components/VuetablePaginationMixin.vue'  => 'vue-table/components/VuetablePaginationMixin.vue',
            'vuejs/vendor/vue-table/components/VuetablePaginationSimple.vue' => 'vue-table/components/VuetablePaginationSimple.vue',
        ];
    }

    private function getVueJsViews()
    {
        return [
            'layouts/flash.stub'        => 'layouts/flash.blade.php',
            'layouts/modal/info.stub'   => 'layouts/modal/info.blade.php',
            'layouts/modal/form.stub'   => 'layouts/modal/form.blade.php',
            'layouts/modal/show.stub'   => 'layouts/modal/show.blade.php',
            'layouts/modal/delete.stub' => 'layouts/modal/delete.blade.php',
        ];
    }

    private function getRequestBaseCustomFile()
    {
        return ['vuejs/request/MyAPIRequest.stub' => 'MyAPIRequest.php'];
    }

    private function updateRoutes()
    {
        $path = config('infyom.laravel_generator.path.routes', app_path('Http/routes.php'));
        $routeContents = file_get_contents($path);

        $routesTemplate = get_template('routes.auth', 'laravel-generator');
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
        $templateData = get_template('home_controller', 'laravel-generator');

        $templateData = $this->fillTemplate($templateData);

        $controllerPath = config('infyom.laravel_generator.path.controller', app_path('Http/Controllers/'));

        $fileName = 'HomeController.php';

        if (file_exists($controllerPath.$fileName)) {
            $answer = $this->ask('Do you want to overwrite '.$fileName.'? (y|N) :', false);

            if (strtolower($answer) != 'y' and strtolower($answer) != 'yes') {
                return;
            }
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

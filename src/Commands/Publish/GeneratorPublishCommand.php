<?php

namespace InfyOm\Generator\Commands\Publish;

use Symfony\Component\Console\Input\InputOption;

class GeneratorPublishCommand extends PublishBaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'infyom:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishes & init api routes, base controller, base test cases traits.';

    public function handle()
    {
        $this->updateRouteServiceProvider();
        $this->publishTestCases();
        $this->publishBaseController();
        $repositoryPattern = config('laravel_generator.options.repository_pattern', true);
        if ($repositoryPattern) {
            $this->publishBaseRepository();
        }
        if ($this->option('localized')) {
            $this->publishLocaleFiles();
        }
    }

    private function updateRouteServiceProvider()
    {
        $routeServiceProviderPath = app_path('Providers'.DIRECTORY_SEPARATOR.'RouteServiceProvider.php');

        if (!file_exists($routeServiceProviderPath)) {
            $this->error("Route Service provider not found on $routeServiceProviderPath");

            return;
        }

        $fileContent = g_filesystem()->getFile($routeServiceProviderPath);

        $search = "Route::middleware('api')".infy_nl().str(' ')->repeat(16)."->prefix('api')";
        $beforeContent = str($fileContent)->before($search);
        $afterContent = str($fileContent)->after($search);

        $finalContent = $beforeContent.$search.infy_nl().str(' ')->repeat(16)."->as('api.')".$afterContent;
        g_filesystem()->createFile($routeServiceProviderPath, $finalContent);
    }

    private function publishTestCases()
    {
        $testsPath = config('laravel_generator.path.tests', base_path('tests/'));
        $testsNameSpace = config('laravel_generator.namespace.tests', 'Tests');
        $createdAtField = config('laravel_generator.timestamps.created_at', 'created_at');
        $updatedAtField = config('laravel_generator.timestamps.updated_at', 'updated_at');

        $templateData = view('laravel-generator::api.test.api_test_trait', [
            'timestamps'      => "['$createdAtField', '$updatedAtField']",
            'namespacesTests' => $testsNameSpace,
        ])->render();

        $fileName = 'ApiTestTrait.php';

        if (file_exists($testsPath.$fileName) && !$this->confirmOverwrite($fileName)) {
            return;
        }

        g_filesystem()->createFile($testsPath.$fileName, $templateData);
        $this->info('ApiTestTrait created');

        $testAPIsPath = config('laravel_generator.path.api_test', base_path('tests/APIs/'));
        if (!file_exists($testAPIsPath)) {
            g_filesystem()->createDirectoryIfNotExist($testAPIsPath);
            $this->info('APIs Tests directory created');
        }

        $testRepositoriesPath = config('laravel_generator.path.repository_test', base_path('tests/Repositories/'));
        if (!file_exists($testRepositoriesPath)) {
            g_filesystem()->createDirectoryIfNotExist($testRepositoriesPath);
            $this->info('Repositories Tests directory created');
        }
    }

    private function publishBaseController()
    {
        $controllerPath = app_path('Http/Controllers/');
        $fileName = 'AppBaseController.php';

        if (file_exists($controllerPath.$fileName) && !$this->confirmOverwrite($fileName)) {
            return;
        }

        $templateData = view('laravel-generator::stubs.app_base_controller', [
            'namespaceApp' => $this->getLaravel()->getNamespace(),
            'apiPrefix'    => config('laravel_generator.api_prefix'),
        ])->render();

        g_filesystem()->createFile($controllerPath.$fileName, $templateData);

        $this->info('AppBaseController created');
    }

    private function publishBaseRepository()
    {
        $repositoryPath = app_path('Repositories/');

        $fileName = 'BaseRepository.php';

        if (file_exists($repositoryPath.$fileName) && !$this->confirmOverwrite($fileName)) {
            return;
        }

        g_filesystem()->createDirectoryIfNotExist($repositoryPath);

        $templateData = view('laravel-generator::stubs.base_repository', [
            'namespaceApp' => $this->getLaravel()->getNamespace(),
        ])->render();

        g_filesystem()->createFile($repositoryPath.$fileName, $templateData);

        $this->info('BaseRepository created');
    }

    private function publishLocaleFiles()
    {
        $localesDir = __DIR__.'/../../../locale/';

        $this->publishDirectory($localesDir, lang_path(), 'lang', true);

        $this->comment('Locale files published');
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

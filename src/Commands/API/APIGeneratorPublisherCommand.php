<?php

namespace InfyOm\Generator\Commands\API;

use InfyOm\Generator\Commands\PublishBaseCommand;
use InfyOm\Generator\Utils\TemplateUtil;

class APIGeneratorPublisherCommand extends PublishBaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'infyom.publish:api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishes api generator routes.';

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $this->publishAPIRoutes();
        $this->initAPIRoutes();
    }

    /**
     * Publishes api_routes.php.
     */
    public function publishAPIRoutes()
    {
        $routesPath = __DIR__.'/../../../templates/api/routes/api_routes.stub';

        $apiRoutesPath = config('infyom.laravel_generator.path.api_routes', app_path('Http/api_routes.php'));

        $this->publishFile($routesPath, $apiRoutesPath, 'api_routes.php');
    }

    /**
     * Initialize routes group based on route integration.
     */
    private function initAPIRoutes()
    {
        $path = config('infyom.laravel_generator.path.routes', app_path('Http/routes.php'));

        $routeContents = file_get_contents($path);

        $template = 'api.routes.api_routes_group';

        $templateData = TemplateUtil::getTemplate($template, 'laravel-generator');

        $templateData = $this->fillTemplate($templateData);

        file_put_contents($path, $routeContents."\n\n".$templateData);
        $this->comment("\nAPI group added to routes.php");
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
        $apiVersion = config('infyom.laravel_generator.api_version', 'v1');
        $apiPrefix = config('infyom.laravel_generator.api_prefix', 'api');
        $apiNamespace = config(
            'infyom.laravel_generator.namespace.api_controller',
            'App\Http\Controllers\API'
        );

        $templateData = str_replace('$API_VERSION$', $apiVersion, $templateData);
        $templateData = str_replace('$NAMESPACE_API_CONTROLLER$', $apiNamespace, $templateData);
        $templateData = str_replace('$API_PREFIX$', $apiPrefix, $templateData);

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

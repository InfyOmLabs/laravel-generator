<?php

namespace InfyOm\Generator\Generators\API;

use Illuminate\Support\Str;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\FileUtil;

class APIRoutesGenerator extends BaseGenerator
{
    private string $routeContents;

    private string $routesTemplate;

    public function __construct()
    {
        parent::__construct();

        $this->path = $this->config->paths->apiRoutes;

        $this->routeContents = FileUtil::getFile($this->path);

        if (!empty($this->config->prefixes->route)) {
            $routesTemplate = get_template('api.routes.prefix_routes', 'laravel-generator');
        } else {
            $routesTemplate = get_template('api.routes.routes', 'laravel-generator');
        }

        $this->routesTemplate = fill_template($this->config->dynamicVars, $routesTemplate);
    }

    public function generate()
    {
        $this->routeContents .= PHP_EOL.PHP_EOL.$this->routesTemplate;
        $existingRouteContents = file_get_contents($this->path);
        if (Str::contains($existingRouteContents, "Route::resource('".$this->config->modelNames->dashedPlural."',")) {
            $this->config->commandInfo(PHP_EOL.'Menu '.$this->config->modelNames->dashedPlural.' already exists, Skipping Adjustment.');

            return;
        }

        file_put_contents($this->path, $this->routeContents);

        $this->config->commandComment(PHP_EOL.$this->config->modelNames->dashedPlural.' api routes added.');
    }

    public function rollback()
    {
        if (Str::contains($this->routeContents, $this->routesTemplate)) {
            $this->routeContents = str_replace($this->routesTemplate, '', $this->routeContents);
            file_put_contents($this->path, $this->routeContents);
            $this->config->commandComment('api routes deleted');
        }
    }
}

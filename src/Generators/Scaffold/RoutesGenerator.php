<?php

namespace InfyOm\Generator\Generators\Scaffold;

use Illuminate\Support\Str;
use InfyOm\Generator\Common\GeneratorConfig;

class RoutesGenerator
{
    private GeneratorConfig $config;

    private string $path;

    private string $routeContents;

    private string $routesTemplate;

    public function __construct(GeneratorConfig $config)
    {
        $this->config = $config;
        $this->path = $this->config->paths->routes;
        $this->routeContents = file_get_contents($this->path);
        if (!empty($this->config->prefixes->route)) {
            $this->routesTemplate = get_template('scaffold.routes.prefix_routes', 'laravel-generator');
        } else {
            $this->routesTemplate = get_template('scaffold.routes.routes', 'laravel-generator');
        }
        $this->routesTemplate = fill_template($this->config->dynamicVars, $this->routesTemplate);
    }

    public function generate()
    {
        $this->routeContents .= "\n\n".$this->routesTemplate;
        $existingRouteContents = file_get_contents($this->path);
        if (Str::contains($existingRouteContents, "Route::resource('".$this->config->modelNames->dashedPlural."',")) {
            $this->config->commandInfo('Route '.$this->config->modelNames->dashedPlural.' is already exists, Skipping Adjustment.');

            return;
        }

        file_put_contents($this->path, $this->routeContents);
        $this->config->commandComment("\n".$this->config->modelNames->dashedPlural.' routes added.');
    }

    public function rollback()
    {
        if (Str::contains($this->routeContents, $this->routesTemplate)) {
            $this->routeContents = str_replace($this->routesTemplate, '', $this->routeContents);
            file_put_contents($this->path, $this->routeContents);
            $this->config->commandComment('scaffold routes deleted');
        }
    }
}

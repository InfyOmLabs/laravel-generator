<?php

namespace InfyOm\Generator\Generators\Scaffold;

use Illuminate\Support\Str;
use InfyOm\Generator\Generators\BaseGenerator;

class RoutesGenerator extends BaseGenerator
{
    private string $routeContents;

    private string $routesTemplate;

    public function __construct()
    {
        parent::__construct();

        $this->path = $this->config->paths->routes;
        $this->routeContents = g_filesystem()->getFile($this->path);
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
            $this->config->commandInfo(PHP_EOL.'Route '.$this->config->modelNames->dashedPlural.' already exists, Skipping Adjustment.');

            return;
        }

        file_put_contents($this->path, $this->routeContents);
        $this->config->commandComment(PHP_EOL.$this->config->modelNames->dashedPlural.' routes added.');
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

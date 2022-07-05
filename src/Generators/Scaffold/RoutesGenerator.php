<?php

namespace InfyOm\Generator\Generators\Scaffold;

use Illuminate\Support\Str;
use InfyOm\Generator\Generators\BaseGenerator;

class RoutesGenerator extends BaseGenerator
{
    public function __construct()
    {
        parent::__construct();

        $this->path = $this->config->paths->routes;
    }

    public function generate()
    {
        $routeContents = g_filesystem()->getFile($this->path);

        $routes = view('laravel-generator::templates.scaffold.routes')->render();

        if (Str::contains($routeContents, $routes)) {
            $this->config->commandInfo(PHP_EOL.'Route '.$this->config->modelNames->dashedPlural.' already exists, Skipping Adjustment.');

            return;
        }

        $routeContents .= infy_nl().$routes;

        g_filesystem()->createFile($this->path, $routeContents);
        $this->config->commandComment(PHP_EOL.$this->config->modelNames->dashedPlural.' routes added.');
    }

    public function rollback()
    {
        $routeContents = g_filesystem()->getFile($this->path);

        $routes = view('laravel-generator::templates.scaffold.routes')->render();

        if (Str::contains($routeContents, $routes)) {
            $routeContents = str_replace($routeContents, '', $routes);
            g_filesystem()->createFile($this->path, $routeContents);
            $this->config->commandComment('scaffold routes deleted');
        }
    }
}

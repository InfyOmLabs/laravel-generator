<?php

namespace InfyOm\Generator\Generators\API;

use Illuminate\Support\Str;
use InfyOm\Generator\Generators\BaseGenerator;

class APIRoutesGenerator extends BaseGenerator
{
    public function __construct()
    {
        parent::__construct();

        $this->path = $this->config->paths->apiRoutes;
    }

    public function generate()
    {
        $routeContents = g_filesystem()->getFile($this->path);

        $routes = view('laravel-generator::api.routes', $this->variables())->render();

        if (Str::contains($routeContents, $routes)) {
            $this->config->commandInfo(PHP_EOL.'Menu '.$this->config->modelNames->dashedPlural.' already exists, Skipping Adjustment.');

            return;
        }

        $routeContents .= PHP_EOL.PHP_EOL.$routes;

        g_filesystem()->createFile($this->path, $routeContents);

        $this->config->commandComment(PHP_EOL.$this->config->modelNames->dashedPlural.' api routes added.');
    }

    public function rollback()
    {
        $routeContents = g_filesystem()->getFile($this->path);

        $routes = view('laravel-generator::api.routes', $this->variables())->render();

        if (Str::contains($routeContents, $routes)) {
            $routeContents = str_replace($routeContents, '', $routes);
            g_filesystem()->createFile($this->path, $routeContents);
            $this->config->commandComment('api routes deleted');
        }
    }
}

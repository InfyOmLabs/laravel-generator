<?php

namespace InfyOm\Generator\DTOs;

use Illuminate\Support\Str;

class GeneratorPrefixes
{
    public string $route = '';
    public string $path = '';
    public string $view = '';
    public string $public = '';
    public string $namespace = '';

    public function mergeRoutePrefix(array $prefixes)
    {
        foreach ($prefixes as $prefix) {
            if (empty($prefix)) {
                continue;
            }

            $this->route .= '.'.Str::camel($prefix);
        }

        $this->route = ltrim($this->route, '.');
    }

    public function mergeNamespacePrefix(array $prefixes)
    {
        foreach ($prefixes as $prefix) {
            if (empty($prefix)) {
                continue;
            }

            $this->namespace .= '\\'.Str::title($prefix);
        }

        $this->namespace = ltrim($this->namespace, '\\');
    }

    private function mergeForwardSlashPrefix(string $initialString, array $prefixes): string
    {
        foreach ($prefixes as $prefix) {
            if (empty($prefix)) {
                continue;
            }

            $initialString .= '/'.Str::title($prefix);
        }

        return ltrim($initialString, '/');
    }

    public function mergePathPrefix(array $prefixes)
    {
        $this->path = $this->mergeForwardSlashPrefix($this->path, $prefixes);
    }

    public function mergeViewPrefix(array $prefixes)
    {
        $this->view = $this->mergeForwardSlashPrefix($this->view, $prefixes);
    }

    public function mergePublicPrefix(array $prefixes)
    {
        $this->public = $this->mergeForwardSlashPrefix($this->public, $prefixes);
    }
}

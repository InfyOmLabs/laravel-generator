<?php

namespace InfyOm\Generator\DTOs;

use Illuminate\Support\Str;

class GeneratorPrefixes
{
    public string $route = '';
    public string $view = '';
    public string $namespace = '';

    public function getRoutePrefixWith($append)
    {
        if ($this->route) {
            return $this->route.$append;
        }

        return '';
    }

    public function getViewPrefixWith($append)
    {
        if ($this->view) {
            return $this->view.$append;
        }

        return '';
    }

    public function getViewPrefixForInclude()
    {
        if ($this->view) {
            return Str::replace('/', '.', $this->view).'.';
        }

        return '';
    }

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

    public function mergeViewPrefix(array $prefixes)
    {
        foreach ($prefixes as $prefix) {
            if (empty($prefix)) {
                continue;
            }

            $this->view .= '/'.Str::snake($prefix);
        }

        $this->view = ltrim($this->view, '/');
    }
}

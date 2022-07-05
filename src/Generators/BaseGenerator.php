<?php

namespace InfyOm\Generator\Generators;

use InfyOm\Generator\Common\GeneratorConfig;

abstract class BaseGenerator
{
    public GeneratorConfig $config;

    public string $path;

    public function __construct()
    {
        $this->config = app(GeneratorConfig::class);
    }

    public function rollbackFile($path, $fileName): bool
    {
        if (file_exists($path.$fileName)) {
            return g_filesystem()->deleteFile($path, $fileName);
        }

        return false;
    }

    public function variables(): array
    {
        return [];
    }
}

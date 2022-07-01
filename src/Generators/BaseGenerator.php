<?php

namespace InfyOm\Generator\Generators;

use InfyOm\Generator\Common\GeneratorConfig;
use InfyOm\Generator\Utils\FileUtil;

class BaseGenerator
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
            return FileUtil::deleteFile($path, $fileName);
        }

        return false;
    }
}

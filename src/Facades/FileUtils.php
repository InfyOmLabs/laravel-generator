<?php

namespace InfyOm\Generator\Facades;

use Illuminate\Support\Facades\Facade;

class FileUtils extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'generator_filesystem';
    }
}

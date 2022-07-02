<?php

namespace InfyOm\Generator\Facades;

use Illuminate\Support\Facades\Facade;
use InfyOm\Generator\Common\FileSystem;
use Mockery;

class FileUtils extends Facade
{
    protected static function getFacadeAccessor()
    {
        return FileSystem::class;
    }

    public static function fake()
    {
        $fake = Mockery::mock()->allows([
            'getFile' => '',
            'createFile' => true,
            'createDirectoryIfNotExist' => true,
            'deleteFile' => true,
        ]);

        static::swap($fake);

        return $fake;
    }
}

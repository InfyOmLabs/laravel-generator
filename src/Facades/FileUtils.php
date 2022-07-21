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

    public static function fake($allowedMethods = [])
    {
        if (empty($allowedMethods)) {
            $allowedMethods = [
                'getFile'                   => '',
                'createFile'                => true,
                'createDirectoryIfNotExist' => true,
                'deleteFile'                => true,
            ];
        }

        static::swap($fake = Mockery::mock()->allows($allowedMethods));

        return $fake;
    }
}

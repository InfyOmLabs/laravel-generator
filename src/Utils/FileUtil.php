<?php

namespace InfyOm\Generator\Utils;

class FileUtil
{
    const FILE_CREATING = 1;
    const FILE_CREATED = 2;
    const FILE_DELETING = 3;
    const FILE_DELETED = 4;

    public static function createFile($path, $fileName, $contents)
    {
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $path = $path.$fileName;

        file_put_contents($path, $contents);
    }

    public static function createDirectoryIfNotExist($path, $replace = false)
    {
        if (file_exists($path) && $replace) {
            rmdir($path);
        }

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
    }

    public static function deleteFile($path, $fileName)
    {
        if (file_exists($path.$fileName)) {
            return unlink($path.$fileName);
        }

        return false;
    }
}

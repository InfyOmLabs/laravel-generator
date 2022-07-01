<?php

namespace InfyOm\Generator\Utils;

class FileUtil
{
    /**
     * Codes for File Operations.
     */
    const FILE_CREATING = 1;
    const FILE_CREATED = 2;
    const FILE_DELETING = 3;
    const FILE_DELETED = 4;

    public static function getFile(string $path): bool|string
    {
        if (!file_exists($path)) {
            return '';
        }

        return file_get_contents($path);
    }

    public static function createFile(string $path, string $fileName, string $contents): bool|int
    {
        if (!empty($path) && !file_exists($path)) {
            return mkdir($path, 0755, true);
        }

        $path = $path.$fileName;

        return file_put_contents($path, $contents);
    }

    public static function createDirectoryIfNotExist(string $path, bool $replace = false): bool
    {
        if (!empty($path) && file_exists($path) && $replace) {
            return rmdir($path);
        }

        if (!empty($path) && !file_exists($path)) {
            return mkdir($path, 0755, true);
        }

        return false;
    }

    public static function deleteFile(string $path, string $fileName): bool
    {
        if (file_exists($path.$fileName)) {
            return unlink($path.$fileName);
        }

        return false;
    }
}

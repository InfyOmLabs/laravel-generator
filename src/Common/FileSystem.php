<?php

namespace InfyOm\Generator\Common;

class FileSystem
{
    public function getFile(string $path): string
    {
        if (!file_exists($path)) {
            return '';
        }

        return file_get_contents($path);
    }

    public function createFile(string $file, string $contents): bool|int
    {
        $path = dirname($file);

        if (!empty($path) && !file_exists($path)) {
            mkdir($path, 0755, true);
        }

        return file_put_contents($file, $contents);
    }

    public function createDirectoryIfNotExist(string $path, bool $replace = false): bool
    {
        if (!empty($path) && file_exists($path) && $replace) {
            return rmdir($path);
        }

        if (!empty($path) && !file_exists($path)) {
            return mkdir($path, 0755, true);
        }

        return false;
    }

    public function deleteFile(string $path, string $fileName): bool
    {
        if (file_exists($path.$fileName)) {
            return unlink($path.$fileName);
        }

        return false;
    }
}

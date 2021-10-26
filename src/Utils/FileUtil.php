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

    /**
     * Create a new File.
     *
     * @param string $path
     * @param string $fileName
     * @param string $contents
     *
     * @return bool|int
     */
    public static function createFile($path, $fileName, $contents)
    {
        if (!empty($path) && !file_exists($path)) {
            return mkdir($path, 0755, true);
        }

        $path = $path.$fileName;

        return file_put_contents($path, $contents);
    }

    /**
     * Create a Directory if it does not exists.
     *
     * @param string $path
     * @param bool   $replace
     *
     * @return bool
     */
    public static function createDirectoryIfNotExist($path, $replace = false)
    {
        if (!empty($path) && file_exists($path) && $replace) {
            return rmdir($path);
        }

        if (!empty($path) && !file_exists($path)) {
            return mkdir($path, 0755, true);
        }

        return false;
    }

    /**
     * Delete a file.
     *
     * @param string $path
     * @param string $fileName
     *
     * @return bool
     */
    public static function deleteFile($path, $fileName)
    {
        if (file_exists($path.$fileName)) {
            return unlink($path.$fileName);
        }

        return false;
    }
}

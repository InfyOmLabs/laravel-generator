<?php

namespace InfyOm\Generator\Generators;

class BaseGenerator
{
    public function rollbackFile($path, $fileName)
    {
        if (file_exists($path.$fileName)) {
            return true;
        }

        return false;
    }
}

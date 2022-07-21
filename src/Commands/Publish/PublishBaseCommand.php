<?php

namespace InfyOm\Generator\Commands\Publish;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishBaseCommand extends Command
{
    public function handle()
    {
        // Do Nothing
    }

    public function publishFile($sourceFile, $destinationFile, $fileName)
    {
        if (file_exists($destinationFile) && !$this->confirmOverwrite($destinationFile)) {
            return;
        }

        copy($sourceFile, $destinationFile);

        $this->comment($fileName.' published');
        $this->info($destinationFile);
    }

    public function publishDirectory(string $sourceDir, string $destinationDir, string $dirName, bool $force = false): bool
    {
        if (file_exists($destinationDir) && !$force && !$this->confirmOverwrite($destinationDir)) {
            return false;
        }

        File::makeDirectory($destinationDir, 493, true, true);
        File::copyDirectory($sourceDir, $destinationDir);

        $this->comment($dirName.' published');
        $this->info($destinationDir);

        return true;
    }

    protected function confirmOverwrite(string $fileName, string $prompt = ''): bool
    {
        $prompt = (empty($prompt))
            ? $fileName.' already exists. Do you want to overwrite it? [y|N]'
            : $prompt;

        return $this->confirm($prompt, false);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return [];
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }
}

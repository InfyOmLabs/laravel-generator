<?php

namespace InfyOm\Generator\Commands\Publish;

use File;
use Illuminate\Console\Command;

class PublishBaseCommand extends Command
{
    public function handle()
    {
    }

    public function publishFile($sourceFile, $destinationFile, $fileName)
    {
        if (file_exists($destinationFile)) {
            $answer = $this->ask('Do you want to overwrite '.$fileName.'? (y|N) :', false);

            if (strtolower($answer) != 'y' and strtolower($answer) != 'yes') {
                return;
            }
        }

        copy($sourceFile, $destinationFile);

        $this->comment($fileName.' published');
        $this->info($destinationFile);
    }

    public function publishDirectory($sourceDir, $destinationDir, $dirName, $force = false)
    {
        if (file_exists($destinationDir)) {
            if (!$force) {
                $answer = $this->ask('Do you want to overwrite '.$dirName.'? (y|N) :', false);

                if (strtolower($answer) != 'y' and strtolower($answer) != 'yes') {
                    return false;
                }
            }
        } else {
            File::makeDirectory($destinationDir, 493, true);
        }

        File::copyDirectory($sourceDir, $destinationDir);

        $this->comment($dirName.' published');
        $this->info($destinationDir);

        return true;
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

<?php

namespace InfyOm\Generator\Commands\Scaffold;

use InfyOm\Generator\Commands\BaseCommand;
use Symfony\Component\Console\Input\InputOption;

class ScaffoldBaseCommand extends BaseCommand
{
    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['paginate', null, InputOption::VALUE_REQUIRED, 'Pagination for index.blade.php'],
        ]);
    }
}

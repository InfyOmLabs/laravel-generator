<?php

namespace InfyOm\Generator\Commands;

use InfyOm\Generator\Common\GeneratorConfig;
use InfyOm\Generator\Utils\FileUtil;

class APIScaffoldGeneratorCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'infyom:api_scaffold';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a full CRUD API and Scaffold for given model';

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        parent::handle();
        $this->fireEvent('api_scaffold', FileUtil::FILE_CREATING);

        $this->generateCommonItems();

        $this->generateAPIItems();

        $this->generateScaffoldItems();

        $this->performPostActionsWithMigration();
        $this->fireEvent('api_scaffold', FileUtil::FILE_CREATED);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return array_merge(parent::getOptions(), []);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(), []);
    }
}

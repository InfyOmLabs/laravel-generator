<?php

namespace InfyOm\Generator\Commands\Common;

use Illuminate\Support\Composer;
use InfyOm\Generator\Commands\BaseCommand;
use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\MigrationGenerator;

class MigrationGeneratorCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'infyom:migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create migration command';

    /**
     * Create a new command instance.
     * @param Composer $composer
     */
    public function __construct(Composer $composer)
    {
        parent::__construct();

        $this->composer = $composer;
        $this->commandData = new CommandData($this, CommandData::$COMMAND_TYPE_API);
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        parent::handle();

        if ($this->commandData->options['fromTable']) {
            $this->error("fromTable option is now allowed to use with migration generator");
            return;
        }

        $migrationGenerator = new MigrationGenerator($this->commandData);
        $migrationGenerator->generate();

        $this->performPostActionsWithMigration();
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

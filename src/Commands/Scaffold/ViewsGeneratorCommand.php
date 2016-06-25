<?php

namespace InfyOm\Generator\Commands\Scaffold;

use InfyOm\Generator\Commands\BaseCommand;
use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\Scaffold\ViewGenerator;

class ViewsGeneratorCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'infyom.scaffold:views';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create views file command';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->commandData = new CommandData($this, CommandData::$COMMAND_TYPE_SCAFFOLD);
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        parent::handle();

        $viewGenerator = new ViewGenerator($this->commandData);
        $viewGenerator->generate();

        $this->performPostActions();
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

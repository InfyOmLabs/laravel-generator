<?php

namespace InfyOm\Generator\Commands\API;

use Illuminate\Support\Composer;
use InfyOm\Generator\Commands\BaseCommand;
use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\API\APIRequestGenerator;

class APIRequestsGeneratorCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'infyom.api:requests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an api request command';

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

        $this->initAPIGeneratorCommandData();

        $controllerGenerator = new APIRequestGenerator($this->commandData);
        $controllerGenerator->generate();

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

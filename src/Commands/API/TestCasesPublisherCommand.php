<?php

namespace InfyOm\Generator\Commands\API;

use InfyOm\Generator\Commands\PublishBaseCommand;

class TestCasesPublisherCommand extends PublishBaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'infyom.publish:tests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishes tests helper files for api tests.';

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $traitPath = __DIR__.'/../../../templates/test/api_test_trait.stub';

        $testsPath = config('infyom.laravel_generator.path.api_test', base_path('tests/'));

        $this->publishFile($traitPath, $testsPath.'ApiTestTrait.php', 'ApiTestTrait.php');

        if (!file_exists($testsPath.'traits/')) {
            mkdir($testsPath.'traits/');
            $this->info('traits directory created');
        }
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

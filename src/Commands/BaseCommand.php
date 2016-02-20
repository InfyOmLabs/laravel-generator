<?php

namespace InfyOm\Generator\Commands;

use Illuminate\Console\Command;
use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Utils\FileUtil;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class BaseCommand extends Command
{
    /**
     * The command Data.
     *
     * @var CommandData
     */
    public $commandData;

    /**
     * @var Composer
     */
    public $composer;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->composer = app()['composer'];
    }

    public function handle()
    {
        $this->commandData->modelName = $this->argument('model');

        $this->commandData->initCommandData();
        $this->commandData->getInputFields();
    }

    public function performPostActions($runMigration = false)
    {
        if ($this->commandData->options['save']) {
            $this->saveSchemaFile();
        }

        if ($runMigration) {
            if ($this->confirm("\nDo you want to migrate database? [y|N]", false)) {
                $this->call('migrate');
            }
        }

        $this->info('Generating autoload files');
        $this->composer->dumpOptimized();
    }

    public function performPostActionsWithMigration()
    {
        $this->performPostActions(true);
    }

    private function saveSchemaFile()
    {
        $fileFields = [];

        foreach ($this->commandData->inputFields as $field) {
            $fileFields[] = [
                'fieldInput'  => $field['fieldInput'],
                'htmlType'    => $field['htmlType'],
                'validations' => $field['validations'],
                'searchable'  => $field['searchable'],
                'fillable'    => $field['fillable'],
                'primary'     => $field['primary'],
            ];
        }

        $path = config('infyom.laravel_generator.path.schema_files', base_path('resources/model_schemas/'));

        $fileName = $this->commandData->modelName.'.json';

        if (file_exists($path.$fileName)) {
            if (!$this->confirm('model file '.$fileName.' already exist. Do you want to overwrite it? [y|N]',
                false)
            ) {
                return;
            }
        }
        FileUtil::createFile($path, $fileName, json_encode($fileFields, JSON_PRETTY_PRINT));
        $this->commandData->commandComment("\nSchema File saved: ");
        $this->commandData->commandInfo($fileName);
    }

    public function initAPIGeneratorCommandData()
    {
        $this->commandData->addDynamicVariable(
            '$NAMESPACE_API_CONTROLLER$',
            config('infyom.laravel_generator.namespace.api_controller', 'App\Http\Controllers\API')
        );

        $this->commandData->addDynamicVariable(
            '$NAMESPACE_API_REQUEST$',
            config('infyom.laravel_generator.namespace.api_request', 'App\Http\Requests\API')
        );
    }

    public function initScaffoldGeneratorCommandData()
    {
        $this->commandData->addDynamicVariable(
            '$NAMESPACE_CONTROLLER$',
            config('infyom.laravel_generator.namespace.controller', 'App\Http\Controllers')
        );

        $this->commandData->addDynamicVariable(
            '$NAMESPACE_REQUEST$',
            config('infyom.laravel_generator.namespace.request', 'App\Http\Requests')
        );

        $this->commandData->setOption('paginate', $this->option('paginate'));
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return [
            ['fieldsFile', null, InputOption::VALUE_REQUIRED, 'Fields input as json file'],
            ['tableName', null, InputOption::VALUE_REQUIRED, 'Table Name'],
            ['fromTable', null, InputOption::VALUE_NONE, 'Generate from existing table'],
            ['save', null, InputOption::VALUE_NONE, 'Save model schema to file'],
            ['primary', null, InputOption::VALUE_REQUIRED, 'Save model schema to file'],
        ];
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['model', InputArgument::REQUIRED, 'Singular Model name'],
        ];
    }
}

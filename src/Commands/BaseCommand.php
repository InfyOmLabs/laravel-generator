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
        if ($this->commandData->getOption('save')) {
            $this->saveSchemaFile();
        }

        if ($runMigration) {
            if ($this->commandData->config->forceMigrate) {
                $this->call('migrate');
            } elseif ($this->confirm("\nDo you want to migrate database? [y|N]", false)) {
                $this->call('migrate');
            }
        }
        if (!$this->commandData->getOption('skipDumpOptimized')) {
            $this->info('Generating autoload files');
            $this->composer->dumpOptimized();
        }
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
                'inForm'      => $field['inForm'],
                'inIndex'     => $field['inIndex'],
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

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return [
            ['fieldsFile', null, InputOption::VALUE_REQUIRED, 'Fields input as json file'],
            ['jsonFromGUI', null, InputOption::VALUE_REQUIRED, 'Direct Json string while using GUI interface'],
            ['tableName', null, InputOption::VALUE_REQUIRED, 'Table Name'],
            ['fromTable', null, InputOption::VALUE_NONE, 'Generate from existing table'],
            ['save', null, InputOption::VALUE_NONE, 'Save model schema to file'],
            ['primary', null, InputOption::VALUE_REQUIRED, 'Custom primary key'],
            ['prefix', null, InputOption::VALUE_REQUIRED, 'Prefix for all files'],
            ['paginate', null, InputOption::VALUE_REQUIRED, 'Pagination for index.blade.php'],
            ['skipDumpOptimized', null, InputOption::VALUE_NONE, 'Skip Composer dump autoload optimized'],
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

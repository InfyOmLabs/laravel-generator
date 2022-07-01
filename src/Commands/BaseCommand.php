<?php

namespace InfyOm\Generator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;
use InfyOm\Generator\Common\GeneratorConfig;
use InfyOm\Generator\Common\GeneratorField;
use InfyOm\Generator\Common\GeneratorFieldRelation;
use InfyOm\Generator\Events\GeneratorFileCreated;
use InfyOm\Generator\Events\GeneratorFileCreating;
use InfyOm\Generator\Events\GeneratorFileDeleted;
use InfyOm\Generator\Events\GeneratorFileDeleting;
use InfyOm\Generator\Generators\API\APIControllerGenerator;
use InfyOm\Generator\Generators\API\APIRequestGenerator;
use InfyOm\Generator\Generators\API\APIResourceGenerator;
use InfyOm\Generator\Generators\API\APIRoutesGenerator;
use InfyOm\Generator\Generators\API\APITestGenerator;
use InfyOm\Generator\Generators\FactoryGenerator;
use InfyOm\Generator\Generators\MigrationGenerator;
use InfyOm\Generator\Generators\ModelGenerator;
use InfyOm\Generator\Generators\RepositoryGenerator;
use InfyOm\Generator\Generators\RepositoryTestGenerator;
use InfyOm\Generator\Generators\Scaffold\ControllerGenerator;
use InfyOm\Generator\Generators\Scaffold\MenuGenerator;
use InfyOm\Generator\Generators\Scaffold\RequestGenerator;
use InfyOm\Generator\Generators\Scaffold\RoutesGenerator;
use InfyOm\Generator\Generators\Scaffold\ViewGenerator;
use InfyOm\Generator\Generators\SeederGenerator;
use InfyOm\Generator\Utils\FileUtil;
use InfyOm\Generator\Utils\GeneratorFieldsInputUtil;
use InfyOm\Generator\Utils\TableFieldsGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class BaseCommand extends Command
{
    public GeneratorConfig $config;

    public Composer $composer;

    public function __construct()
    {
        parent::__construct();

        $this->composer = app()['composer'];
    }

    public function handle()
    {
        $this->config = app(GeneratorConfig::class);
        $this->config->setCommand($this);

        $this->config->init();
        $this->getFields();
    }

    public function generateCommonItems()
    {
        if (!$this->option('fromTable') and !$this->isSkip('migration')) {
            $migrationGenerator = new MigrationGenerator();
            $migrationGenerator->generate();
        }

        if (!$this->isSkip('model')) {
            $modelGenerator = new ModelGenerator();
            $modelGenerator->generate();
        }

        if (!$this->isSkip('repository') && $this->config->options->repositoryPattern) {
            $repositoryGenerator = new RepositoryGenerator();
            $repositoryGenerator->generate();
        }

        if ($this->config->options->factory || (!$this->isSkip('tests') and $this->config->addons->tests)) {
            $factoryGenerator = new FactoryGenerator();
            $factoryGenerator->generate();
        }

        if ($this->config->options->seeder) {
            $seederGenerator = new SeederGenerator();
            $seederGenerator->generate();
        }
    }

    public function generateAPIItems()
    {
        if (!$this->isSkip('requests') and !$this->isSkip('api_requests')) {
            $requestGenerator = new APIRequestGenerator();
            $requestGenerator->generate();
        }

        if (!$this->isSkip('controllers') and !$this->isSkip('api_controller')) {
            $controllerGenerator = new APIControllerGenerator();
            $controllerGenerator->generate();
        }

        if (!$this->isSkip('routes') and !$this->isSkip('api_routes')) {
            $routesGenerator = new APIRoutesGenerator();
            $routesGenerator->generate();
        }

        if (!$this->isSkip('tests') and $this->config->addons->tests) {
            if ($this->config->options->repositoryPattern) {
                $repositoryTestGenerator = new RepositoryTestGenerator();
                $repositoryTestGenerator->generate();
            }

            $apiTestGenerator = new APITestGenerator();
            $apiTestGenerator->generate();
        }
        if ($this->config->options->resources) {
            $apiResourceGenerator = new APIResourceGenerator();
            $apiResourceGenerator->generate();
        }
    }

    public function generateScaffoldItems()
    {
        if (!$this->isSkip('requests') and !$this->isSkip('scaffold_requests')) {
            $requestGenerator = new RequestGenerator();
            $requestGenerator->generate();
        }

        if (!$this->isSkip('controllers') and !$this->isSkip('scaffold_controller')) {
            $controllerGenerator = new ControllerGenerator();
            $controllerGenerator->generate();
        }

        if (!$this->isSkip('views')) {
            $viewGenerator = new ViewGenerator();
            $viewGenerator->generate();
        }

        if (!$this->isSkip('routes') and !$this->isSkip('scaffold_routes')) {
            $routeGenerator = new RoutesGenerator();
            $routeGenerator->generate();
        }

        if (!$this->isSkip('menu')) {
            $menuGenerator = new MenuGenerator();
            $menuGenerator->generate();
        }
    }

    public function performPostActions($runMigration = false)
    {
        if ($this->config->options->saveSchemaFile) {
            $this->saveSchemaFile();
        }

        if ($runMigration) {
            if ($this->option('forceMigrate')) {
                $this->runMigration();
            } elseif (!$this->option('fromTable') and !$this->isSkip('migration')) {
                $requestFromConsole = (php_sapi_name() == 'cli') ? true : false;
                if ($this->option('jsonFromGUI') && $requestFromConsole) {
                    $this->runMigration();
                } elseif ($requestFromConsole && $this->confirm("\nDo you want to migrate database? [y|N]", false)) {
                    $this->runMigration();
                }
            }
        }

        if ($this->config->options->localized) {
            $this->saveLocaleFile();
        }

        if (!$this->isSkip('dump-autoload')) {
            $this->info('Generating autoload files');
            $this->composer->dumpOptimized();
        }
    }

    public function runMigration()
    {
        $migrationPath = config('laravel_generator.path.migration', database_path('migrations/'));
        $path = Str::after($migrationPath, base_path()); // get path after base_path
        $this->call('migrate', ['--path' => $path, '--force' => true]);

        return true;
    }

    public function isSkip($skip)
    {
        if ($this->option('skip')) {
            return in_array($skip, (array) $this->option('skip'));
        }

        return false;
    }

    public function performPostActionsWithMigration()
    {
        $this->performPostActions(true);
    }

    private function saveSchemaFile()
    {
        $fileFields = [];

        foreach ($this->config->fields as $field) {
            $fileFields[] = [
                'name'        => $field->name,
                'dbType'      => $field->dbInput,
                'htmlType'    => $field->htmlInput,
                'validations' => $field->validations,
                'searchable'  => $field->isSearchable,
                'fillable'    => $field->isFillable,
                'primary'     => $field->isPrimary,
                'inForm'      => $field->inForm,
                'inIndex'     => $field->inIndex,
                'inView'      => $field->inView,
            ];
        }

        foreach ($this->config->relations as $relation) {
            $fileFields[] = [
                'type'     => 'relation',
                'relation' => $relation->type.','.implode(',', $relation->inputs),
            ];
        }

        $path = config('laravel_generator.path.schema_files', resource_path('model_schemas/'));

        $fileName = $this->config->modelNames->name.'.json';

        if (file_exists($path.$fileName) && !$this->confirmOverwrite($fileName)) {
            return;
        }
        FileUtil::createFile($path, $fileName, json_encode($fileFields, JSON_PRETTY_PRINT));
        $this->comment("\nSchema File saved: ");
        $this->info($fileName);
    }

    private function saveLocaleFile()
    {
        $locales = [
            'singular' => $this->config->modelNames->name,
            'plural'   => $this->config->modelNames->plural,
            'fields'   => [],
        ];

        foreach ($this->config->fields as $field) {
            $locales['fields'][$field->name] = Str::title(str_replace('_', ' ', $field->name));
        }

        $path = config('laravel_generator.path.models_locale_files', base_path('resources/lang/en/models/'));

        $fileName = $this->config->modelNames->plural.'.php';

        if (file_exists($path.$fileName) && !$this->confirmOverwrite($fileName)) {
            return;
        }
        $content = "<?php\n\nreturn ".var_export($locales, true).';'.\PHP_EOL;
        FileUtil::createFile($path, $fileName, $content);
        $this->comment("\nModel Locale File saved: ");
        $this->info($fileName);
    }

    /**
     * @param $fileName
     * @param string $prompt
     *
     * @return bool
     */
    protected function confirmOverwrite($fileName, $prompt = '')
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
        return [
            ['fieldsFile', null, InputOption::VALUE_REQUIRED, 'Fields input as json file'],
            ['jsonFromGUI', null, InputOption::VALUE_REQUIRED, 'Direct Json string while using GUI interface'],
            ['plural', null, InputOption::VALUE_REQUIRED, 'Plural Model name'],
            ['table', null, InputOption::VALUE_REQUIRED, 'Table Name'],
            ['fromTable', null, InputOption::VALUE_REQUIRED, 'Generate from existing table'],
            ['ignoreFields', null, InputOption::VALUE_REQUIRED, 'Ignore fields while generating from table'],
            ['primary', null, InputOption::VALUE_REQUIRED, 'Custom primary key'],
            ['prefix', null, InputOption::VALUE_REQUIRED, 'Prefix for all files'],
            ['skip', null, InputOption::VALUE_REQUIRED, 'Skip Specific Items to Generate (migration,model,controllers,api_controller,scaffold_controller,repository,requests,api_requests,scaffold_requests,routes,api_routes,scaffold_routes,views,tests,menu,dump-autoload)'],
            ['views', null, InputOption::VALUE_REQUIRED, 'Specify only the views you want generated: index,create,edit,show'],
            ['relations', null, InputOption::VALUE_NONE, 'Specify if you want to pass relationships for fields'],
            ['forceMigrate', null, InputOption::VALUE_NONE, 'Specify if you want to run migration or not'],
            ['connection', null, InputOption::VALUE_REQUIRED, 'Specify connection name'],
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

    public function getFields()
    {
        $this->fields = [];

        if ($this->option('fieldsFile')) {
            $this->parseFieldsFromJsonFile();

            return;
        }

        if ($this->option('jsonFromGUI')) {
            $this->parseFieldsFromGUI();

            return;
        }

        if ($this->option('fromTable')) {
            $this->parseFieldsFromTable();

            return;
        }

        $this->getFieldsFromConsole();
    }

    private function getFieldsFromConsole()
    {
        $this->info('Specify fields for the model (skip id & timestamp fields, we will add it automatically)');
        $this->info('Read docs carefully to specify field inputs)');
        $this->info('Enter "exit" to finish');

        $this->addPrimaryKey();

        while (true) {
            $fieldInputStr = $this->ask('Field: (name db_type html_type options)', '');

            if (empty($fieldInputStr) || $fieldInputStr == false || $fieldInputStr == 'exit') {
                break;
            }

            if (!GeneratorFieldsInputUtil::validateFieldInput($fieldInputStr)) {
                $this->error('Invalid Input. Try again');
                continue;
            }

            $validations = $this->ask('Enter validations: ', false);
            $validations = ($validations == false) ? '' : $validations;

            if ($this->option('relations')) {
                $relation = $this->ask('Enter relationship (Leave Blank to skip):', false);
            } else {
                $relation = '';
            }

            $this->config->fields[] = GeneratorFieldsInputUtil::processFieldInput(
                $fieldInputStr,
                $validations
            );

            if (!empty($relation)) {
                $this->config->relations[] = GeneratorFieldRelation::parseRelation($relation);
            }
        }

        if (config('laravel_generator.timestamps.enabled', true)) {
            $this->addTimestamps();
        }
    }

    private function addPrimaryKey()
    {
        $primaryKey = new GeneratorField();
        if ($this->option('primary')) {
            $primaryKey->name = $this->option('primary') ?? 'id';
        } else {
            $primaryKey->name = 'id';
        }
        $primaryKey->parseDBType('id');
        $primaryKey->parseOptions('s,f,p,if,ii');

        $this->config->fields[] = $primaryKey;
    }

    private function addTimestamps()
    {
        $createdAt = new GeneratorField();
        $createdAt->name = 'created_at';
        $createdAt->parseDBType('timestamp');
        $createdAt->parseOptions('s,f,if,ii');
        $this->config->fields[] = $createdAt;

        $updatedAt = new GeneratorField();
        $updatedAt->name = 'updated_at';
        $updatedAt->parseDBType('timestamp');
        $updatedAt->parseOptions('s,f,if,ii');
        $this->config->fields[] = $updatedAt;
    }

    private function parseFieldsFromJsonFile()
    {
        $fieldsFileValue = $this->option('fieldsFile');
        if (file_exists($fieldsFileValue)) {
            $filePath = $fieldsFileValue;
        } elseif (file_exists(base_path($fieldsFileValue))) {
            $filePath = base_path($fieldsFileValue);
        } else {
            $schemaFileDirector = config(
                'laravel_generator.path.schema_files',
                resource_path('model_schemas/')
            );
            $filePath = $schemaFileDirector.$fieldsFileValue;
        }

        if (!file_exists($filePath)) {
            $this->error('Fields file not found');
            exit;
        }

        $fileContents = file_get_contents($filePath);
        $jsonData = json_decode($fileContents, true);
        $this->config->fields = [];
        foreach ($jsonData as $field) {
            $this->config->fields[] = GeneratorField::parseFieldFromFile($field);

            if (isset($field['relation'])) {
                $this->config->relations[] = GeneratorFieldRelation::parseRelation($field['relation']);
            }
        }
    }

    private function parseFieldsFromGUI()
    {
        $fileContents = $this->option('jsonFromGUI');
        $jsonData = json_decode($fileContents, true);

        // override config options from jsonFromGUI
        $this->config->overrideOptionsFromJsonFile($jsonData);

        // Manage custom table name option
        if (isset($jsonData['tableName'])) {
            $tableName = $jsonData['tableName'];
            $this->config->tableName = $tableName;
            $this->config->addDynamicVariable('$TABLE_NAME$', $tableName);
            $this->config->addDynamicVariable('$TABLE_NAME_TITLE$', Str::studly($tableName));
        }

        // Manage migrate option
        if (isset($jsonData['migrate']) && $jsonData['migrate'] == false) {
            $this->config->options['skip'][] = 'migration';
        }

        foreach ($jsonData['fields'] as $field) {
            if (isset($field['type']) && $field['relation']) {
                $this->config->relations[] = GeneratorFieldRelation::parseRelation($field['relation']);
            } else {
                $this->config->fields[] = GeneratorField::parseFieldFromFile($field);
                if (isset($field['relation'])) {
                    $this->config->relations[] = GeneratorFieldRelation::parseRelation($field['relation']);
                }
            }
        }
    }

    private function parseFieldsFromTable()
    {
        $tableName = $this->config->tableName;

        $ignoredFields = $this->option('ignoreFields');
        if (!empty($ignoredFields)) {
            $ignoredFields = explode(',', trim($ignoredFields));
        } else {
            $ignoredFields = [];
        }

        $tableFieldsGenerator = new TableFieldsGenerator($tableName, $ignoredFields, $this->config->connection);
        $tableFieldsGenerator->prepareFieldsFromTable();
        $tableFieldsGenerator->prepareRelations();

        $this->config->fields = $tableFieldsGenerator->fields;
        $this->config->relations = $tableFieldsGenerator->relations;
    }

    private function prepareEventsData()
    {
        $data['modelName'] = $this->config->modelNames->name;
        $data['tableName'] = $this->config->tableName;
        $data['nsModel'] = $this->config->namespaces->model;

        return $data;
    }

    public function fireEvent(string $commandType, int $eventType)
    {
        switch ($eventType) {
            case FileUtil::FILE_CREATING:
                event(new GeneratorFileCreating($commandType, $this->prepareEventsData()));
                break;
            case FileUtil::FILE_CREATED:
                event(new GeneratorFileCreated($commandType, $this->prepareEventsData()));
                break;
            case FileUtil::FILE_DELETING:
                event(new GeneratorFileDeleting($commandType, $this->prepareEventsData()));
                break;
            case FileUtil::FILE_DELETED:
                event(new GeneratorFileDeleted($commandType, $this->prepareEventsData()));
                break;
        }
    }
}

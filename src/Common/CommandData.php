<?php

namespace InfyOm\Generator\Common;

use Exception;
use Illuminate\Console\Command;
use InfyOm\Generator\Utils\GeneratorFieldsInputUtil;
use InfyOm\Generator\Utils\TableFieldsGenerator;

class CommandData
{
    public static $COMMAND_TYPE_API = 'api';
    public static $COMMAND_TYPE_SCAFFOLD = 'scaffold';
    public static $COMMAND_TYPE_API_SCAFFOLD = 'api_scaffold';
    public static $COMMAND_TYPE_VUEJS = 'vuejs';

    /** @var string */
    public $modelName;
    public $commandType;

    /** @var GeneratorConfig */
    public $config;

    /** @var GeneratorField[] */
    public $fields = [];

    /** @var GeneratorFieldRelation[] */
    public $relations = [];

    /** @var Command */
    public $commandObj;

    /** @var array */
    public $dynamicVars = [];
    public $fieldNamesMapping = [];

    /** @var CommandData */
    protected static $instance = null;

    public static function getInstance()
    {
        return self::$instance;
    }

    /**
     * @param Command $commandObj
     * @param string  $commandType
     *
     * @return CommandData
     */
    public function __construct(Command $commandObj, $commandType)
    {
        $this->commandObj = $commandObj;
        $this->commandType = $commandType;

        $this->fieldNamesMapping = [
            '$FIELD_NAME_TITLE$' => 'fieldTitle',
            '$FIELD_NAME$'       => 'name',
        ];

        $this->config = new GeneratorConfig();
    }

    public function commandError($error)
    {
        $this->commandObj->error($error);
    }

    public function commandComment($message)
    {
        $this->commandObj->comment($message);
    }

    public function commandWarn($warning)
    {
        $this->commandObj->warn($warning);
    }

    public function commandInfo($message)
    {
        $this->commandObj->info($message);
    }

    public function initCommandData()
    {
        $this->config->init($this);
    }

    public function getOption($option)
    {
        return $this->config->getOption($option);
    }

    public function getAddOn($option)
    {
        return $this->config->getAddOn($option);
    }

    public function setOption($option, $value)
    {
        $this->config->setOption($option, $value);
    }

    public function addDynamicVariable($name, $val)
    {
        $this->dynamicVars[$name] = $val;
    }

    public function getFields()
    {
        $this->fields = [];

        if ($this->getOption('fieldsFile') or $this->getOption('jsonFromGUI')) {
            $this->getInputFromFileOrJson();
        } elseif ($this->getOption('fromTable')) {
            $this->getInputFromTable();
        } else {
            $this->getInputFromConsole();
        }
    }

    private function getInputFromConsole()
    {
        $this->commandInfo('Specify fields for the model (skip id & timestamp fields, we will add it automatically)');
        $this->commandInfo('Read docs carefully to specify field inputs)');
        $this->commandInfo('Enter "exit" to finish');

        $this->addPrimaryKey();

        while (true) {
            $fieldInputStr = $this->commandObj->ask('Field: (name db_type html_type options)', '');

            if (empty($fieldInputStr) || $fieldInputStr == false || $fieldInputStr == 'exit') {
                break;
            }

            if (!GeneratorFieldsInputUtil::validateFieldInput($fieldInputStr)) {
                $this->commandError('Invalid Input. Try again');
                continue;
            }

            $validations = $this->commandObj->ask('Enter validations: ', false);
            $validations = ($validations == false) ? '' : $validations;

            if ($this->getOption('relations')) {
                $relation = $this->commandObj->ask('Enter relationship (Leave Black to skip):', false);
            } else {
                $relation = '';
            }

            $this->fields[] = GeneratorFieldsInputUtil::processFieldInput(
                $fieldInputStr,
                $validations
            );

            if (!empty($relation)) {
                $this->relations[] = GeneratorFieldRelation::parseRelation($relation);
            }
        }

        $this->addTimestamps();
    }

    private function addPrimaryKey()
    {
        $primaryKey = new GeneratorField();
        if ($this->getOption('primary')) {
            $primaryKey->name = $this->getOption('primary');
        } else {
            $primaryKey->name = 'id';
        }
        $primaryKey->parseDBType('increments');
        $primaryKey->parseOptions('s,f,p,if,ii');

        $this->fields[] = $primaryKey;
    }

    private function addTimestamps()
    {
        $createdAt = new GeneratorField();
        $createdAt->name = 'created_at';
        $createdAt->parseDBType('timestamp');
        $createdAt->parseOptions('s,f,if,ii');
        $this->fields[] = $createdAt;

        $updatedAt = new GeneratorField();
        $updatedAt->name = 'updated_at';
        $updatedAt->parseDBType('timestamp');
        $updatedAt->parseOptions('s,f,if,ii');
        $this->fields[] = $updatedAt;
    }

    private function getInputFromFileOrJson()
    {
        // fieldsFile option will get high priority than json option if both options are passed
        try {
            if ($this->getOption('fieldsFile')) {
                $fieldsFileValue = $this->getOption('fieldsFile');
                if (file_exists($fieldsFileValue)) {
                    $filePath = $fieldsFileValue;
                } elseif (file_exists(base_path($fieldsFileValue))) {
                    $filePath = base_path($fieldsFileValue);
                } else {
                    $schemaFileDirector = config('infyom.laravel_generator.path.schema_files');
                    $filePath = $schemaFileDirector.$fieldsFileValue;
                }

                if (!file_exists($filePath)) {
                    $this->commandError('Fields file not found');
                    exit;
                }

                $fileContents = file_get_contents($filePath);
                $jsonData = json_decode($fileContents, true);
                $this->fields = [];
                foreach ($jsonData as $field) {
                    if (isset($field['type']) && $field['relation']) {
                        $this->relations[] = GeneratorFieldRelation::parseRelation($field['relation']);
                    } else {
                        $this->fields[] = GeneratorField::parseFieldFromFile($field);
                        if (isset($field['relation'])) {
                            $this->relations[] = GeneratorFieldRelation::parseRelation($field['relation']);
                        }
                    }
                }
            } else {
                $fileContents = $this->getOption('jsonFromGUI');
                $jsonData = json_decode($fileContents, true);
                foreach ($jsonData['fields'] as $field) {
                    if (isset($field['type']) && $field['relation']) {
                        $this->relations[] = GeneratorFieldRelation::parseRelation($field['relation']);
                    } else {
                        $this->fields[] = GeneratorField::parseFieldFromFile($field);
                        if (isset($field['relation'])) {
                            $this->relations[] = GeneratorFieldRelation::parseRelation($field['relation']);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $this->commandError($e->getMessage());
            exit;
        }
    }

    private function getInputFromTable()
    {
        $tableName = $this->dynamicVars['$TABLE_NAME$'];

        $tableFieldsGenerator = new TableFieldsGenerator($tableName);
        $tableFieldsGenerator->prepareFieldsFromTable();
        $tableFieldsGenerator->prepareRelations();

        $this->fields = $tableFieldsGenerator->fields;
        $this->relations = $tableFieldsGenerator->relations;
    }
}

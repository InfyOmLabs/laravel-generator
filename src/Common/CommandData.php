<?php

namespace InfyOm\Generator\Common;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use InfyOm\Generator\Utils\GeneratorFieldsInputUtil;
use InfyOm\Generator\Utils\TableFieldsGenerator;

class CommandData
{
    public static $COMMAND_TYPE_API = 'api';
    public static $COMMAND_TYPE_SCAFFOLD = 'scaffold';
    public static $COMMAND_TYPE_SCAFFOLD_API = 'scaffold_api';

    /** @var string */
    public $modelName, $commandType;

    /** @var array */
    public $modelNames, $commandOptions, $inputFields;

    /** @var Command */
    public $commandObj;

    /** @var array */
    public $dynamicVars = [], $fieldNamesMapping = [], $options = [], $addOns = [];

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
            '$FIELD_NAME$'       => 'fieldName',
        ];
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
        $this->prepareModelNames();
        $this->prepareOptions();
        $this->prepareAddOns();
        $this->initDynamicVariables();
        $this->addDynamicVariable('$NAMESPACE_APP$', $this->commandObj->getLaravel()->getNamespace());
    }

    private function prepareModelNames()
    {
        $this->modelNames['plural'] = Str::plural($this->modelName);
        $this->modelNames['camel'] = Str::camel($this->modelName);
        $this->modelNames['camelPlural'] = Str::camel($this->modelNames['plural']);
        $this->modelNames['snake'] = Str::snake($this->modelName);
        $this->modelNames['snakePlural'] = Str::snake($this->modelNames['plural']);
    }

    private function prepareOptions()
    {
        $options = ['fieldsFile', 'tableName', 'fromTable', 'save', 'primary'];

        foreach ($options as $option) {
            $this->options[$option] = $this->commandObj->option($option);
        }

        if ($this->options['fromTable']) {
            if (!$this->options['tableName']) {
                $this->commandError('tableName required with fromTable option.');
                exit;
            }
        }

        $this->options['softDelete'] = config('infyom.laravel_generator.options.softDelete', true);
    }

    public function getOption($option)
    {
        if (isset($this->options[$option])) {
            return $this->options[$option];
        }

        return false;
    }

    public function setOption($option, $value)
    {
        $this->options[$option] = $value;
    }

    private function prepareAddOns()
    {
        $this->addOns['swagger'] = config('infyom.laravel_generator.add_on.swagger', false);
        $this->addOns['tests'] = config('infyom.laravel_generator.add_on.tests', false);
    }

    public function getAddOn($addOn)
    {
        if (isset($this->addOns[$addOn])) {
            return $this->addOns[$addOn];
        }

        return false;
    }

    private function initDynamicVariables()
    {
        $this->dynamicVars = $this->getConfigDynamicVariables();

        if ($this->options['tableName']) {
            $tableName = $this->options['tableName'];
        } else {
            $tableName = $this->modelNames['snakePlural'];
        }

        $this->dynamicVars = array_merge(
            $this->dynamicVars,
            [
                '$MODEL_NAME$'              => $this->modelName,
                '$MODEL_NAME_CAMEL$'        => $this->modelNames['camel'],
                '$MODEL_NAME_PLURAL$'       => $this->modelNames['plural'],
                '$MODEL_NAME_PLURAL_CAMEL$' => $this->modelNames['camelPlural'],
                '$MODEL_NAME_SNAKE$'        => $this->modelNames['snake'],
                '$MODEL_NAME_PLURAL_SNAKE$' => $this->modelNames['snakePlural'],
                '$TABLE_NAME$'              => $tableName,
                '$API_PREFIX$'              => config('infyom.laravel_generator.api_prefix', 'api'),
                '$API_VERSION$'             => config('infyom.laravel_generator.api_version', 'v1'),
            ]
        );
    }

    private function getConfigDynamicVariables()
    {
        return [
            '$NAMESPACE_REPOSITORY$'   => config('infyom.laravel_generator.namespace.repository', 'App\Repositories'),
            '$NAMESPACE_MODEL$'        => config('infyom.laravel_generator.namespace.model', 'App\Models'),
            '$NAMESPACE_MODEL_EXTEND$' => config(
                'infyom.laravel_generator.model_extend_class',
                'Illuminate\Database\Eloquent\Model'
            ),
            '$SOFT_DELETE_DATES$'  => "\n\tprotected \$dates = ['deleted_at'];\n",
            '$SOFT_DELETE$'        => "use SoftDeletes;\n",
            '$SOFT_DELETE_IMPORT$' => "use Illuminate\\Database\\Eloquent\\SoftDeletes;\n",
        ];
    }

    public function addDynamicVariable($name, $val)
    {
        $this->dynamicVars[$name] = $val;
    }

    public function getInputFields()
    {
        $this->inputFields = [];

        if ($this->options['fieldsFile']) {
            $this->getInputFromFile();
        } elseif ($this->options['fromTable']) {
            $this->getInputFromTable();
        } else {
            $this->getInputFromConsole();
        }
    }

    private function getInputFromConsole()
    {
        $this->commandInfo('Specify fields for the model (skip id & timestamp fields, we will add it automatically)');
        $this->commandInfo('Enter "exit" to finish');

        $this->addPrimaryKey();

        while (true) {
            $fieldInputStr = $this->commandObj->ask('Field: (field_name:field_database_type)', '');

            if (empty($fieldInputStr) || $fieldInputStr == false || $fieldInputStr == 'exit') {
                break;
            }

            if (!GeneratorFieldsInputUtil::validateFieldInput($fieldInputStr)) {
                $this->commandError('Invalid Input. Try again');
                continue;
            }

            if ($this->commandType == self::$COMMAND_TYPE_SCAFFOLD or
                $this->commandType == self::$COMMAND_TYPE_SCAFFOLD_API
            ) {
                $htmlType = $this->commandObj->ask('Enter field html input type (text): ', 'text');
            } else {
                $htmlType = '';
            }

            $validations = $this->commandObj->ask('Enter validations: ', false);
            $searchable = $this->commandObj->ask('Is Searchable (y/N): ', false);

            $validations = ($validations == false) ? '' : $validations;

            if ($searchable) {
                $searchable = (strtolower($searchable) == 'y') ? true : false;
            }

            $this->inputFields[] = GeneratorFieldsInputUtil::processFieldInput($fieldInputStr, $htmlType, $validations,
                $searchable);
        }

        $this->addTimestamps();
    }

    private function addPrimaryKey()
    {
        if ($this->options['primary']) {
            $this->inputFields[] = GeneratorFieldsInputUtil::processFieldInput($this->options['primary'].':increments', '', '', false, false, true);
        } else {
            $this->inputFields[] = GeneratorFieldsInputUtil::processFieldInput('id:increments', '', '', false, false, true);
        }
    }

    private function addTimestamps()
    {
        $this->inputFields[] = GeneratorFieldsInputUtil::processFieldInput('created_at:timestamp', '', '', false, false);
        $this->inputFields[] = GeneratorFieldsInputUtil::processFieldInput('updated_at:timestamp', '', '', false, false);
    }

    private function getInputFromFile()
    {
        try {
            if (file_exists($this->options['fieldsFile'])) {
                $filePath = $this->options['fieldsFile'];
            } else {
                $filePath = base_path($this->options['fieldsFile']);
            }

            if (!file_exists($filePath)) {
                $this->commandError('Fields file not found');
                exit;
            }

            $fileContents = file_get_contents($filePath);
            $fields = json_decode($fileContents, true);

            $this->inputFields = array_merge($this->inputFields, GeneratorFieldsInputUtil::validateFieldsFile($fields));
            $this->checkForDiffPrimaryKey();
        } catch (Exception $e) {
            $this->commandError($e->getMessage());
            exit;
        }
    }

    private function checkForDiffPrimaryKey()
    {
        foreach ($this->inputFields as $field) {
            if (isset($field['primary']) && $field['primary'] && $field['fieldName'] != 'id') {
                $this->options['primary'] = $field['fieldName'];
                break;
            }
        }
    }

    private function getInputFromTable()
    {
        $tableName = $this->dynamicVars['$TABLE_NAME$'];

        $this->inputFields = TableFieldsGenerator::generateFieldsFromTable($tableName);
        $this->checkForDiffPrimaryKey();
    }
}

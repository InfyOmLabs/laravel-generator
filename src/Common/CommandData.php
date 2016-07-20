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

    /** @var string */
    public $modelName, $commandType;

    /** @var GeneratorConfig */
    public $config;

    /** @var array */
    public $inputFields;

    /** @var Command */
    public $commandObj;

    /** @var array */
    public $dynamicVars = [], $fieldNamesMapping = [];

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

    public function getInputFields()
    {
        $this->inputFields = [];

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
                $this->commandType == self::$COMMAND_TYPE_API_SCAFFOLD
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

            $this->inputFields[] = GeneratorFieldsInputUtil::processFieldInput(
                $fieldInputStr,
                $htmlType,
                $validations,
                ['searchable' => $searchable]
            );
        }

        $this->addTimestamps();
    }

    private function addPrimaryKey()
    {
        if ($this->getOption('primary')) {
            $this->inputFields[] = GeneratorFieldsInputUtil::processFieldInput(
                $this->getOption('primary').':increments',
                '',
                '',
                [
                    'searchable' => false,
                    'fillable'   => false,
                    'primary'    => true,
                    'inForm'     => false,
                    'inIndex'    => false,
                ]
            );
        } else {
            $this->inputFields[] = GeneratorFieldsInputUtil::processFieldInput(
                'id:increments',
                '',
                '',
                [
                    'searchable' => false,
                    'fillable'   => false,
                    'primary'    => true,
                    'inForm'     => false,
                    'inIndex'    => false,
                ]
            );
        }
    }

    private function addTimestamps()
    {
        $this->inputFields[] = GeneratorFieldsInputUtil::processFieldInput(
            'created_at:timestamp',
            '',
            '',
            [
                'searchable' => false,
                'fillable'   => false,
                'inForm'     => false,
                'inIndex'    => false,
            ]
        );

        $this->inputFields[] = GeneratorFieldsInputUtil::processFieldInput(
            'updated_at:timestamp',
            '',
            '',
            [
                'searchable' => false,
                'fillable'   => false,
                'inForm'     => false,
                'inIndex'    => false,
            ]
        );
    }

    private function getInputFromFileOrJson()
    {
        // fieldsFile option will get high priority than json option if both options are passed
        try {
            if ($this->getOption('fieldsFile')) {
                if (file_exists($this->getOption('fieldsFile'))) {
                    $filePath = $this->getOption('fieldsFile');
                } else {
                    $filePath = base_path($this->getOption('fieldsFile'));
                }

                if (!file_exists($filePath)) {
                    $this->commandError('Fields file not found');
                    exit;
                }

                $fileContents = file_get_contents($filePath);
                $jsonData = json_decode($fileContents, true);
                $this->inputFields = array_merge($this->inputFields, GeneratorFieldsInputUtil::validateFieldsFile($jsonData));
            } else {
                $fileContents = $this->getOption('jsonFromGUI');
                $jsonData = json_decode($fileContents, true);
                $this->inputFields = array_merge($this->inputFields, GeneratorFieldsInputUtil::validateFieldsFile($jsonData['fields']));
                $this->config->overrideOptionsFromJsonFile($jsonData);
                if (isset($jsonData['migrate'])) {
                    $this->config->forceMigrate = $jsonData['migrate'];
                }
            }

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
                $this->setOption('primary', $field['fieldName']);
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

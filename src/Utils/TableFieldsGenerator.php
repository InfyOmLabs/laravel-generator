<?php

namespace InfyOm\Generator\Utils;

use DB;
use InfyOm\Generator\Common\GeneratorField;
use InfyOm\Generator\Common\GeneratorFieldRelation;

class TableFieldsGenerator
{
    /** @var  string */
    public $table, $primaryKey;

    /** @var  boolean */
    public $defaultSearchable;

    /** @var  array */
    public $timestamps;

    /** @var \Doctrine\DBAL\Schema\AbstractSchemaManager */
    private $schemaManager;

    /** @var  \Doctrine\DBAL\Schema\Column[] */
    private $columns;

    /** @var  GeneratorField[] */
    public $fields;

    /** @var  GeneratorFieldRelation[] */
    public $relations;

    public function __construct($tableName)
    {
        $this->schemaManager = DB::getDoctrineSchemaManager();
        $platform = $this->schemaManager->getDatabasePlatform();
        $platform->registerDoctrineTypeMapping('enum', 'string');

        $this->columns = $this->schemaManager->listTableColumns($tableName);

        $this->primaryKey = static::getPrimaryKeyOfTable($tableName);
        $this->timestamps = static::getTimestampFieldNames();
        $this->defaultSearchable = config('infyom.laravel_generator.options.tables_searchable_default', false);
    }

    public function prepareFieldsFromTable()
    {
        foreach ($this->columns as $column) {

            $type = $column->getType()->getName();

            switch ($type) {
                case 'integer':
                    $field = $this->generateIntFieldInput($column, 'integer');
                    break;
                case 'smallint':
                    $field = $this->generateIntFieldInput($column, 'smallInteger');
                    break;
                case 'bigint':
                    $field = $this->generateIntFieldInput($column, 'bigInteger');
                    break;
                case 'boolean':
                    $name = title_case(str_replace("_", " ", $column->getName()));
                    $field = $this->generateField($column, 'bigInteger', 'checkbox,'.$name.",1");
                    break;
                case 'datetime':
                    $field = $this->generateField($column, 'datetime', 'date');
                    break;
                case 'datetimetz':
                    $field = $this->generateField($column, 'dateTimeTz', 'date');
                    break;
                case 'date':
                    $field = $this->generateField($column, 'date', 'date');
                    break;
                case 'time':
                    $field = $this->generateField($column, 'time', 'text');
                    break;
                case 'decimal':
                    $field = $this->generateNumberInput($column, 'decimal');
                    break;
                case 'float':
                    $field = $this->generateNumberInput($column, 'float');
                    break;
                case 'string':
                    $field = $this->generateField($column, 'string', 'text');
                    break;
                case 'text':
                    $field = $this->generateField($column, 'text', 'textarea`');
                    break;
                default:
                    $field = $this->generateField($column, 'string', 'text');
                    break;
            }

            if (strtolower($field->name) == 'password') {
                $field->htmlType = 'password';
            } elseif (strtolower($field->name) == 'email') {
                $field->htmlType = 'email';
            } elseif (in_array($field->name, $this->timestamps)) {
                $field->isSearchable = false;
                $field->isFillable = false;
                $field->inForm = false;
                $field->inIndex = false;
            }

            $this->fields[] = $field;
        }
    }

    /**
     * @param string $tableName
     *
     * @return string|null The column name of the (simple) primary key
     */
    public static function getPrimaryKeyOfTable($tableName)
    {
        $schema = DB::getDoctrineSchemaManager();
        $column = $schema->listTableDetails($tableName)->getPrimaryKey();

        return $column ? $column->getColumns()[0] : '';
    }

    /**
     * @return array the set of [created_at column name, updated_at column name]
     */
    public static function getTimestampFieldNames()
    {
        if (!config('infyom.laravel_generator.timestamps.enabled', true)) {
            return [];
        }

        $createdAtName = config('infyom.laravel_generator.timestamps.created_at', 'created_at');
        $updatedAtName = config('infyom.laravel_generator.timestamps.updated_at', 'updated_at');
        $deletedAtName = config('infyom.laravel_generator.timestamps.deleted_at', 'deleted_at');

        return [$createdAtName, $updatedAtName, $deletedAtName];
    }

    /**
     * @param string $dbType
     * @param \Doctrine\DBAL\Schema\Column $column
     * @return GeneratorField
     */
    private function generateIntFieldInput($column, $dbType)
    {
        $field = new GeneratorField();
        $field->name = $column->getName();
        $field->parseDBType($dbType);
        $field->htmlType = "number";

        if ($column->getAutoincrement()) {
            $field->dbInput .= ',true';
        } else {
            $field->dbInput .= ',false';
        }

        if ($column->getUnsigned()) {
            $field->dbInput .= ',true';
        }

        return $this->checkForPrimary($field);
    }

    /**
     * @param GeneratorField $field
     * @return GeneratorField
     */
    private function checkForPrimary(GeneratorField $field)
    {
        if ($field->name == $this->primaryKey) {
            $field->isPrimary = true;
            $field->isFillable = false;
            $field->isSearchable = false;
            $field->inIndex = false;
            $field->inForm = false;
        }

        return $field;
    }

    /**
     * @param \Doctrine\DBAL\Schema\Column $column
     * @param $dbType
     * @param $htmlType
     * @return GeneratorField
     */
    private function generateField($column, $dbType, $htmlType)
    {
        $field = new GeneratorField();
        $field->name = $column->getName();
        $field->parseDBType($dbType);
        $field->htmlType = $htmlType;

        return $this->checkForPrimary($field);
    }

    /**
     * @param \Doctrine\DBAL\Schema\Column $column
     * @param string $dbType
     * @return GeneratorField
     */
    private function generateNumberInput($column, $dbType)
    {
        $field = new GeneratorField();
        $field->name = $column->getName();
        $field->parseDBType($dbType.',' . $column->getPrecision() . ',' . $column->getScale());
        $field->htmlType = "number";

        return $this->checkForPrimary($field);
    }
}

<?php

namespace InfyOm\Generator\Utils;

use DB;

class TableFieldsGenerator
{
    public static function generateFieldsFromTable($tableName)
    {
        $schema = DB::getDoctrineSchemaManager();
        $platform = $schema->getDatabasePlatform();
        $platform->registerDoctrineTypeMapping('enum', 'string');

        $columns = $schema->listTableColumns($tableName);

        $primaryKey = static::getPrimaryKeyFromTable($tableName);
        $timestamps = static::getTimestampFieldNames();
        $defaultSearchable = config('infyom.laravel_generator.options.tables_searchable_default', false);

        $fields = [];

        foreach ($columns as $column) {
            switch ($column->getType()->getName()) {
                case 'integer':
                    $fieldInput = self::generateIntFieldInput($column->getName(), 'integer', $column);
                    $type = 'number';
                    break;
                case 'smallint':
                    $fieldInput = self::generateIntFieldInput($column->getName(), 'smallInteger', $column);
                    $type = 'number';
                    break;
                case 'bigint':
                    $fieldInput = self::generateIntFieldInput($column->getName(), 'bigInteger', $column);
                    $type = 'number';
                    break;
                case 'boolean':
                    $fieldInput = self::generateSingleFieldInput($column->getName(), 'boolean');
                    $type = 'text';
                    break;
                case 'datetime':
                    $fieldInput = self::generateSingleFieldInput($column->getName(), 'dateTime');
                    $type = 'date';
                    break;
                case 'datetimetz':
                    $fieldInput = self::generateSingleFieldInput($column->getName(), 'dateTimeTz');
                    $type = 'date';
                    break;
                case 'date':
                    $fieldInput = self::generateSingleFieldInput($column->getName(), 'date');
                    $type = 'date';
                    break;
                case 'time':
                    $fieldInput = self::generateSingleFieldInput($column->getName(), 'time');
                    $type = 'text';
                    break;
                case 'decimal':
                    $fieldInput = self::generateDecimalInput($column);
                    $type = 'number';
                    break;
                case 'float':
                    $fieldInput = self::generateFloatInput($column);
                    $type = 'number';
                    break;
                case 'string':
                    $fieldInput = self::generateStringInput($column);
                    $type = 'text';
                    break;
                case 'text':
                    $fieldInput = self::generateTextInput($column);
                    $type = 'textarea';
                    break;
                default:
                    $fieldInput = self::generateTextInput($column);
                    $type = 'text';
            }

            if (strtolower($column->getName()) == 'password') {
                $type = 'password';
            } elseif (strtolower($column->getName()) == 'email') {
                $type = 'email';
            }

            if (!empty($fieldInput)) {
                $field = GeneratorFieldsInputUtil::processFieldInput(
                    $fieldInput,
                    $type,
                    '',
                    ['searchable' => $defaultSearchable]
                );

                $columnName = $column->getName();

                if ($columnName === $primaryKey) {
                    $field['primary'] = true;
                    $field['inFrom'] = false;
                    $field['inIndex'] = false;
                    $field['fillable'] = false;
                    $field['searchable'] = false;
                } elseif (in_array($columnName, $timestamps)) {
                    $field['fillable'] = false;
                    $field['searchable'] = false;
                    $field['inFrom'] = true;
                    $field['inIndex'] = true;
                }

                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * @param string $tableName
     *
     * @return string|null The column name of the (simple) primary key
     */
    public static function getPrimaryKeyFromTable($tableName)
    {
        $schema = DB::getDoctrineSchemaManager();
        $indexes = collect($schema->listTableIndexes($tableName));

        $primaryKey = $indexes->first(function ($i, $index) {
            return $index->isPrimary() && 1 === count($index->getColumns());
        });

        return !empty($primaryKey) ? $primaryKey->getColumns()[0] : null;
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

        return [$createdAtName, $updatedAtName];
    }

    /**
     * @param string $name
     * @param string $type
     * @param \Doctrine\DBAL\Schema\Column $column
     *
     * @return string
     */
    private static function generateIntFieldInput($name, $type, $column)
    {
        $fieldInput = "$name:$type";

        if ($column->getAutoincrement()) {
            $fieldInput .= ',true';
        }

        if ($column->getUnsigned()) {
            $fieldInput .= ',true';
        }

        return $fieldInput;
    }

    private static function generateSingleFieldInput($name, $type)
    {
        $fieldInput = "$name:$type";

        return $fieldInput;
    }

    /**
     * @param \Doctrine\DBAL\Schema\Column $column
     *
     * @return string
     */
    private static function generateDecimalInput($column)
    {
        $fieldInput = $column->getName() . ':decimal,' . $column->getPrecision() . ',' . $column->getScale();

        return $fieldInput;
    }

    /**
     * @param \Doctrine\DBAL\Schema\Column $column
     *
     * @return string
     */
    private static function generateFloatInput($column)
    {
        $fieldInput = $column->getName() . ':float,' . $column->getPrecision() . ',' . $column->getScale();

        return $fieldInput;
    }

    /**
     * @param \Doctrine\DBAL\Schema\Column $column
     * @param int $length
     *
     * @return string
     */
    private static function generateStringInput($column, $length = 255)
    {
        $fieldInput = $column->getName() . ':string,' . $length;

        return $fieldInput;
    }

    /**
     * @param \Doctrine\DBAL\Schema\Column $column
     *
     * @return string
     */
    private static function generateTextInput($column)
    {
        $fieldInput = $column->getName() . ':text';

        return $fieldInput;
    }
}

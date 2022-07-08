<?php

namespace InfyOm\Generator\Generators;

use InfyOm\Generator\Common\GeneratorField;

class SwaggerGenerator
{
    public static function generateTypes(array $inputFields): array
    {
        $fieldTypes = [];

        /** @var GeneratorField $field */
        foreach ($inputFields as $field) {
            $fieldData = self::getFieldType($field->dbType);

            if (empty($fieldData['fieldType'])) {
                continue;
            }

            $fieldTypes[] = [
                'fieldName'   => $field->name,
                'type'        => $fieldData['fieldType'],
                'format'      => $fieldData['fieldFormat'],
                'nullable'    => !$field->isNotNull ? 'true' : 'false',
                'readOnly'    => !$field->isFillable ? 'true' : 'false',
                'description' => (!empty($field->description)) ? $field->description : '',
            ];
        }

        return $fieldTypes;
    }

    public static function getFieldType($type): array
    {
        $fieldType = null;
        $fieldFormat = null;
        switch (strtolower($type)) {
            case 'increments':
            case 'integer':
            case 'unsignedinteger':
            case 'smallinteger':
            case 'long':
            case 'biginteger':
            case 'unsignedbiginteger':
                $fieldType = 'integer';
                $fieldFormat = 'int32';
                break;
            case 'double':
            case 'float':
            case 'real':
            case 'decimal':
                $fieldType = 'number';
                $fieldFormat = 'number';
                break;
            case 'boolean':
                $fieldType = 'boolean';
                break;
            case 'string':
            case 'char':
            case 'text':
            case 'mediumtext':
            case 'longtext':
            case 'enum':
                $fieldType = 'string';
                break;
            case 'byte':
                $fieldType = 'string';
                $fieldFormat = 'byte';
                break;
            case 'binary':
                $fieldType = 'string';
                $fieldFormat = 'binary';
                break;
            case 'password':
                $fieldType = 'string';
                $fieldFormat = 'password';
                break;
            case 'date':
                $fieldType = 'string';
                $fieldFormat = 'date';
                break;
            case 'datetime':
            case 'timestamp':
                $fieldType = 'string';
                $fieldFormat = 'date-time';
                break;
        }

        return ['fieldType' => $fieldType, 'fieldFormat' => $fieldFormat];
    }
}

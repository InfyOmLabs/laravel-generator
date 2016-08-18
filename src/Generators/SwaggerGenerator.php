<?php

namespace InfyOm\Generator\Generators;

use InfyOm\Generator\Common\GeneratorField;

class SwaggerGenerator
{
    public static $swaggerTypes = [];

    /**
     * @param GeneratorField[] $inputFields
     *
     * @return array
     */
    public static function generateTypes($inputFields)
    {
        if (!empty(self::$swaggerTypes)) {
            return self::$swaggerTypes;
        }

        $fieldTypes = [];

        foreach ($inputFields as $field) {
            $fieldFormat = '';
            switch (strtolower($field->fieldType)) {
                case 'integer':
                case 'increments':
                case 'smallinteger':
                case 'long':
                case 'bigint':
                    $fieldType = 'integer';
                    $fieldFormat = 'int32';
                    break;
                case 'double':
                    $fieldType = 'number';
                    $fieldFormat = 'double';
                    break;
                case 'float':
                case 'decimal':
                    $fieldType = 'number';
                    $fieldFormat = 'float';
                    break;
                case 'boolean':
                    $fieldType = 'boolean';
                    break;
                case 'string':
                case 'char':
                case 'text':
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
                case 'dateTime':
                case 'timestamp':
                    $fieldType = 'string';
                    $fieldFormat = 'date-time';
                    break;
                default:
                    $fieldType = null;
                    $fieldFormat = null;
                    break;
            }

            if (!empty($fieldType)) {
                $fieldType = [
                    'name'   => $field->name,
                    'type'   => $fieldType,
                    'format' => $fieldFormat,
                ];

//                if (isset($field['description'])) {
//                    $fieldType['description'] = $field['description'];
//                } else {
                $fieldType['description'] = '';
//                }

                $fieldTypes[] = $fieldType;
            }
        }

        self::$swaggerTypes = $fieldTypes;

        return self::$swaggerTypes;
    }

    public static function generateSwagger($fields, $fillables, $variables)
    {
        $template = get_template('model.model', 'swagger-generator');

        $templateData = fill_template($variables, $template);

        $templateData = str_replace('$REQUIRED_FIELDS$', '"'.implode('", "', $fillables).'"', $templateData);

        $propertyTemplate = get_template('model.property', 'swagger-generator');

        $properties = self::preparePropertyFields($propertyTemplate, $fields);

        $templateData = str_replace('$PROPERTIES$', implode(",\n", $properties), $templateData);

        return $templateData;
    }

    /**
     * @param $template
     * @param $fields
     *
     * @return array
     */
    public static function preparePropertyFields($template, $fields)
    {
        $templates = [];

        foreach ($fields as $field) {
            $fieldName = $field['name'];
            $type = $field['type'];
            $format = $field['format'];
            $propertyTemplate = str_replace('$FIELD_NAME$', $fieldName, $template);
            $description = $field['description'];
            if (empty($description)) {
                $description = $fieldName;
            }
            $propertyTemplate = str_replace('$DESCRIPTION$', $description, $propertyTemplate);
            $propertyTemplate = str_replace('$FIELD_TYPE$', $type, $propertyTemplate);
            if (!empty($format)) {
                $format = ",\n *          format=\"".$format.'"';
            }
            $propertyTemplate = str_replace('$FIELD_FORMAT$', $format, $propertyTemplate);
            $templates[] = $propertyTemplate;
        }

        return $templates;
    }
}

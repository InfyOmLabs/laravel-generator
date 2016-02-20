<?php

namespace InfyOm\Generator\Utils;

use Illuminate\Support\Str;
use RuntimeException;

class GeneratorFieldsInputUtil
{
    public static function validateFieldsFile($fields)
    {
        $fieldsArr = [];

        foreach ($fields as $field) {
            if (!self::validateFieldInput($field['fieldInput'])) {
                throw new RuntimeException('Invalid Input '.$field['fieldInput']);
            }

            if (isset($field['htmlType'])) {
                $htmlType = $field['htmlType'];
            } else {
                $htmlType = 'text';
            }

            if (isset($field['validations'])) {
                $validations = $field['validations'];
            } else {
                $validations = '';
            }

            if (isset($field['searchable'])) {
                $searchable = $field['searchable'];
            } else {
                $searchable = false;
            }

            if (isset($field['searchable'])) {
                $fillable = $field['fillable'];
            } else {
                $fillable = false;
            }

            if (isset($field['primary'])) {
                $primary = $field['primary'];
            } else {
                $primary = false;
            }

            $fieldsArr[] = self::processFieldInput($field['fieldInput'], $htmlType, $validations, $searchable, $fillable, $primary);
        }

        return $fieldsArr;
    }

    public static function validateFieldInput($fieldInputStr)
    {
        $fieldInputs = explode(':', $fieldInputStr);

        if (count($fieldInputs) < 2) {
            return false;
        }

        return true;
    }

    public static function processFieldInput($fieldInput, $htmlType, $validations, $searchable = false, $fillable = true, $primary = false)
    {
        $fieldInputs = explode(':', $fieldInput);

        $fieldName = array_shift($fieldInputs);
        $databaseInputs = implode(':', $fieldInputs);
        $fieldType = explode(',', $fieldInputs[0])[0];

        $htmlTypeInputs = explode(':', $htmlType);
        $htmlType = array_shift($htmlTypeInputs);

        if (count($htmlTypeInputs) > 0) {
            $htmlTypeInputs = array_shift($htmlTypeInputs);
        }

        return [
            'fieldInput'     => $fieldInput,
            'fieldTitle'     => Str::title(str_replace('_', ' ', $fieldName)),
            'fieldType'      => $fieldType,
            'fieldName'      => $fieldName,
            'databaseInputs' => $databaseInputs,
            'htmlType'       => $htmlType,
            'htmlTypeInputs' => $htmlTypeInputs,
            'validations'    => $validations,
            'searchable'     => $searchable,
            'fillable'       => $fillable,
            'primary'        => $primary,
        ];
    }

    public static function prepareKeyValueArrayStr($arr)
    {
        $arrStr = '[';
        foreach ($arr as $item) {
            $arrStr .= "'$item' => '$item', ";
        }

        $arrStr = substr($arrStr, 0, strlen($arrStr) - 2);

        $arrStr .= ']';

        return $arrStr;
    }

    public static function prepareValuesArrayStr($arr)
    {
        $arrStr = '[';
        foreach ($arr as $item) {
            $arrStr .= "'$item', ";
        }

        $arrStr = substr($arrStr, 0, strlen($arrStr) - 2);

        $arrStr .= ']';

        return $arrStr;
    }
}

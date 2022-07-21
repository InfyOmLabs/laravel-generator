<?php

namespace InfyOm\Generator\Utils;

class GeneratorFieldsInputUtil
{
    public static function validateFieldInput($fieldInputStr): bool
    {
        $fieldInputs = explode(' ', $fieldInputStr);

        if (count($fieldInputs) < 2) {
            return false;
        }

        return true;
    }

    /**
     * Prepare string of associative array.
     */
    public static function prepareKeyValueArrayStr(array $arr): string
    {
        $arrStr = '[';
        if (count($arr) > 0) {
            foreach ($arr as $key => $item) {
                $arrStr .= "'$item' => '$key', ";
            }
            $arrStr = substr($arrStr, 0, strlen($arrStr) - 2);
        }

        $arrStr .= ']';

        return $arrStr;
    }

    /**
     * Prepare string of array.
     */
    public static function prepareValuesArrayStr(array $arr): string
    {
        $arrStr = '[';
        if (count($arr) > 0) {
            foreach ($arr as $item) {
                $arrStr .= "'$item', ";
            }
            $arrStr = substr($arrStr, 0, strlen($arrStr) - 2);
        }

        $arrStr .= ']';

        return $arrStr;
    }

    public static function prepareKeyValueArrFromLabelValueStr($values): array
    {
        $arr = [];
        if (count($values) > 0) {
            foreach ($values as $value) {
                $labelValue = explode(':', $value);

                if (count($labelValue) > 1) {
                    $arr[$labelValue[0]] = $labelValue[1];
                } else {
                    $arr[$labelValue[0]] = $labelValue[0];
                }
            }
        }

        return $arr;
    }
}

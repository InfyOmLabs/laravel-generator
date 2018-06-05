<?php

namespace InfyOm\Generator\Utils;

use InfyOm\Generator\Common\GeneratorField;

class GeneratorFieldsInputUtil
{
    public static function validateFieldInput($fieldInputStr)
    {
        $fieldInputs = explode(' ', $fieldInputStr);

        if (count($fieldInputs) < 2) {
            return false;
        }

        return true;
    }

    /**
     * @param $fieldInput
     * @param $validations
     *
     * @return GeneratorField
     */
    public static function processFieldInput($fieldInput, $validations)
    {
        /*
         * Field Input Format: field_name <space> db_type <space> html_type(optional) <space> options(optional)
         * Options are to skip the field from certain criteria like searchable, fillable, not in form, not in index
         * Searchable (s), Fillable (f), In Form (if), In Index (ii)
         * Sample Field Inputs
         *
         * title string text
         * body text textarea
         * name string,20 text
         * post_id integer:unsigned:nullable
         * post_id integer:unsigned:nullable:foreign,posts,id
         * password string text if,ii,s - options will skip field from being added in form, in index and searchable
         */

        $fieldInputsArr = explode(' ', $fieldInput);

        $field = new GeneratorField();
        $field->name = $fieldInputsArr[0];
        $field->parseDBType($fieldInputsArr[1]);

        if (count($fieldInputsArr) > 2) {
            $field->parseHtmlInput($fieldInputsArr[2]);
        }

        if (count($fieldInputsArr) > 3) {
            $field->parseOptions($fieldInputsArr[3]);
        }

        $field->validations = $validations;

        return $field;
    }

    public static function prepareKeyValueArrayStr($arr)
    {
        $arrStr = '[';
        foreach ($arr as $key => $item) {
            $arrStr .= "'$item' => '$key', ";
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

    public static function prepareKeyValueArrFromLabelValueStr($values)
    {
        $arr = [];

        foreach ($values as $value) {
            $labelValue = explode(':', $value);

            if (count($labelValue) > 1) {
                $arr[$labelValue[0]] = $labelValue[1];
            } else {
                $arr[$labelValue[0]] = $labelValue[0];
            }
        }

        return $arr;
    }
}

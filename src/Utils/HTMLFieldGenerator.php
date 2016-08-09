<?php

namespace InfyOm\Generator\Utils;

use InfyOm\Generator\Common\GeneratorField;

class HTMLFieldGenerator
{
    public static function generateHTML(GeneratorField $field, $templateType)
    {
        $fieldTemplate = '';

        switch($field->htmlType) {
            case 'text':
            case 'textarea':
            case 'date':
            case 'file':
            case 'email':
            case 'password':
                $fieldTemplate = get_template('scaffold.fields.'.$field->htmlType, $templateType);
                break;
            case 'number':
                $fieldTemplate = get_template('scaffold.fields.' . $field->htmlType, $templateType);
                break;
            case 'select':
            case 'enum':
                $fieldTemplate = get_template('scaffold.fields.select', $templateType);
                $inputsArr = GeneratorFieldsInputUtil::prepareKeyValueArrFromLabelValueStr($field->htmlValues);

                $fieldTemplate = str_replace(
                    '$INPUT_ARR$',
                    GeneratorFieldsInputUtil::prepareKeyValueArrayStr($inputsArr),
                    $fieldTemplate
                );
                break;
        }

        return $fieldTemplate;
    }

}
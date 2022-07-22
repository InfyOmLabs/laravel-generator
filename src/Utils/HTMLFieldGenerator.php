<?php

namespace InfyOm\Generator\Utils;

use Illuminate\Support\Str;
use InfyOm\Generator\Common\GeneratorField;

class HTMLFieldGenerator
{
    public static function generateHTML(GeneratorField $field, $templateType): string
    {
        $viewName = $field->htmlType;
        $variables = [];

        if (!empty($validations = self::generateValidations($field))) {
            $variables['options'] = ', '.implode(', ', $validations);
        }

        switch ($field->htmlType) {
            case 'select':
            case 'enum':
                $viewName = 'select';
                $keyValues = GeneratorFieldsInputUtil::prepareKeyValueArrFromLabelValueStr($field->htmlValues);

                $variables = [
                    'selectValues' => GeneratorFieldsInputUtil::prepareKeyValueArrayStr($keyValues),
                ];
                break;
            case 'checkbox':
                if (count($field->htmlValues) > 0) {
                    $checkboxValue = $field->htmlValues[0];
                } else {
                    $checkboxValue = 1;
                }
                $variables['checkboxVal'] = $checkboxValue;
                break;
            case 'radio':
                $keyValues = GeneratorFieldsInputUtil::prepareKeyValueArrFromLabelValueStr($field->htmlValues);

                $radioButtons = [];
                foreach ($keyValues as $label => $value) {
                    $radioButtons[] = view($templateType.'.fields.radio', [
                        'label'     => $label,
                        'value'     => $value,
                        'fieldName' => $field->name,
                    ]);
                }

                return view($templateType.'.fields.radio_group', array_merge(
                    ['radioButtons' => implode(infy_nl_tab(), $radioButtons)],
                    array_merge(
                        $field->variables(),
                        $variables
                    )
                ))->render();
        }

        return view(
            $templateType.'.fields.'.$viewName,
            array_merge(
                $field->variables(),
                $variables
            )
        )->render();
    }

    public static function generateValidations(GeneratorField $field)
    {
        $validations = explode('|', $field->validations);
        $validationRules = [];

        foreach ($validations as $validation) {
            if ($validation === 'required') {
                $validationRules[] = "'required'";
                continue;
            }

            if (!Str::contains($validation, ['max:', 'min:'])) {
                continue;
            }

            $validationText = substr($validation, 0, 3);
            $sizeInNumber = substr($validation, 4);

            $sizeText = ($validationText == 'min') ? 'minlength' : 'maxlength';
            if ($field->htmlType == 'number') {
                $sizeText = $validationText;
            }

            $size = "'$sizeText' => $sizeInNumber";
            $validationRules[] = $size;
        }

        return $validationRules;
    }
}

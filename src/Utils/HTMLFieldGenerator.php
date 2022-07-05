<?php

namespace InfyOm\Generator\Utils;

use InfyOm\Generator\Common\GeneratorField;

class HTMLFieldGenerator
{
    public static function generateHTML(GeneratorField $field, $templateType): string
    {
        $viewName = $field->htmlType;
        $variables = [];

        switch ($field->htmlType) {
            case 'select':
            case 'enum':
                $viewName = 'select';
                $radioLabels = GeneratorFieldsInputUtil::prepareKeyValueArrFromLabelValueStr($field->htmlValues);

                $variables = [
                    'selectValues' => GeneratorFieldsInputUtil::prepareKeyValueArrayStr($radioLabels),
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
                $radioLabels = GeneratorFieldsInputUtil::prepareKeyValueArrFromLabelValueStr($field->htmlValues);

                $radioButtons = [];
                foreach ($radioLabels as $label => $value) {
                    $radioButtons[] = view($templateType.'.fields.radio', [
                        'label'     => $label,
                        'value'     => $value,
                        'fieldName' => $field->name,
                    ]);
                }

                return view($templateType.'.fields.radio_group', array_merge(
                    ['radioButtons' => implode(infy_nl(), $radioButtons)],
                    $field->variables()
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
}

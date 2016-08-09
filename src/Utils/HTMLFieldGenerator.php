<?php

namespace InfyOm\Generator\Utils;

use InfyOm\Generator\Common\GeneratorField;

class HTMLFieldGenerator
{
    public static function generateHTML(GeneratorField $field)
    {
        switch($field->htmlType) {
            case 'text':
                $fieldTemplate = get_template('scaffold.fields.'.$field->htmlType, '');
                break;
            case 'textarea':
                break;
            case 'text':
                break;
            case 'text':
                break;
            case 'text':
                break;
        }
    }

}
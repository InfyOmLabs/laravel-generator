<?php

use Illuminate\Support\Str;
use InfyOm\Generator\Common\GeneratorField;

if (!function_exists('infy_tab')) {
    /**
     * Generates tab with spaces.
     *
     * @param int $spaces
     *
     * @return string
     */
    function infy_tab($spaces = 4)
    {
        return str_repeat(' ', $spaces);
    }
}

if (!function_exists('infy_tabs')) {
    /**
     * Generates tab with spaces.
     *
     * @param int $tabs
     * @param int $spaces
     *
     * @return string
     */
    function infy_tabs($tabs, $spaces = 4)
    {
        return str_repeat(infy_tab($spaces), $tabs);
    }
}

if (!function_exists('infy_nl')) {
    /**
     * Generates new line char.
     *
     * @param int $count
     *
     * @return string
     */
    function infy_nl($count = 1)
    {
        return str_repeat(PHP_EOL, $count);
    }
}

if (!function_exists('infy_nls')) {
    /**
     * Generates new line char.
     *
     * @param int $count
     * @param int $nls
     *
     * @return string
     */
    function infy_nls($count, $nls = 1)
    {
        return str_repeat(infy_nl($nls), $count);
    }
}

if (!function_exists('infy_nl_tab')) {
    /**
     * Generates new line char.
     *
     * @param int $lns
     * @param int $tabs
     *
     * @return string
     */
    function infy_nl_tab($lns = 1, $tabs = 1)
    {
        return infy_nls($lns).infy_tabs($tabs);
    }
}

if (!function_exists('get_template_file_path')) {
    /**
     * get path for template file.
     *
     * @param string $templateName
     * @param string $templateType
     *
     * @return string
     */
    function get_template_file_path($templateName, $templateType)
    {
        $templateName = str_replace('.', '/', $templateName);

        $templatesPath = config(
            'infyom.laravel_generator.path.templates_dir',
            resource_path('infyom/infyom-generator-templates/')
        );

        $path = $templatesPath.$templateName.'.stub';

        if (file_exists($path)) {
            return $path;
        }

        return get_templates_package_path($templateType).'/templates/'.$templateName.'.stub';
    }
}

if (!function_exists('get_templates_package_path')) {
    /**
     * Finds templates package's full path.
     *
     * @param string $templateType
     *
     * @return string
     */
    function get_templates_package_path($templateType)
    {
        if (strpos($templateType, '/') === false) {
            $templateType = base_path('vendor/infyomlabs/').$templateType;
        }

        return $templateType;
    }
}

if (!function_exists('get_template')) {
    /**
     * get template contents.
     *
     * @param string $templateName
     * @param string $templateType
     *
     * @return string
     */
    function get_template($templateName, $templateType)
    {
        $path = get_template_file_path($templateName, $templateType);

        return file_get_contents($path);
    }
}

if (!function_exists('fill_template')) {
    /**
     * fill template with variable values.
     *
     * @param array  $variables
     * @param string $template
     *
     * @return string
     */
    function fill_template($variables, $template)
    {
        foreach ($variables as $variable => $value) {
            $template = str_replace($variable, $value, $template);
        }

        return $template;
    }
}

if (!function_exists('fill_field_template')) {
    /**
     * fill field template with variable values.
     *
     * @param array          $variables
     * @param string         $template
     * @param GeneratorField $field
     *
     * @return string
     */
    function fill_field_template($variables, $template, $field)
    {
        foreach ($variables as $variable => $key) {
            $template = str_replace($variable, $field->$key, $template);
        }

        return $template;
    }
}

if (!function_exists('fill_template_with_field_data')) {
    /**
     * fill template with field data.
     *
     * @param array          $variables
     * @param array          $fieldVariables
     * @param string         $template
     * @param GeneratorField $field
     *
     * @return string
     */
    function fill_template_with_field_data($variables, $fieldVariables, $template, $field)
    {
        $template = fill_template($variables, $template);

        return fill_field_template($fieldVariables, $template, $field);
    }
}

if (!function_exists('fill_template_with_field_data_locale')) {
    /**
     * fill template with field data.
     *
     * @param array          $variables
     * @param array          $fieldVariables
     * @param string         $template
     * @param GeneratorField $field
     *
     * @return string
     */
    function fill_template_with_field_data_locale($variables, $fieldVariables, $template, $field)
    {
        $template = fill_template($variables, $template);
        $modelName = $variables['$MODEL_NAME_PLURAL_CAMEL$'];

        return fill_field_template_locale($fieldVariables, $template, $field, $modelName);
    }
}

if (!function_exists('fill_field_template_locale')) {
    /**
     * fill field template with variable values.
     *
     * @param array          $variables
     * @param string         $template
     * @param GeneratorField $field
     * @param string         $modelName
     *
     * @return string
     */
    function fill_field_template_locale($variables, $template, $field, $modelName)
    {
        foreach ($variables as $variable => $key) {
            $value = $field->name;
            $template = str_replace($variable, "@lang('models/$modelName.fields.$value')", $template);
        }

        return $template;
    }
}

if (!function_exists('model_name_from_table_name')) {
    /**
     * generates model name from table name.
     *
     * @param string $tableName
     *
     * @return string
     */
    function model_name_from_table_name($tableName)
    {
        return Str::ucfirst(Str::camel(Str::singular($tableName)));
    }
}

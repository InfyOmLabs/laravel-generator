<?php
namespace InfyOm\Generator\Utils;

class InfyOmHelpers
{
    /**
     * Generates tab with spaces.
     *
     * @param int $spaces
     *
     * @return string
     */
    public static function infy_tab($spaces = 4)
    {
        return str_repeat(' ', $spaces);
    }

    /**
     * Generates tab with spaces.
     *
     * @param int $tabs
     * @param int $spaces
     *
     * @return string
     */
    public static function infy_tabs($tabs, $spaces = 4)
    {
        return str_repeat(InfyOmHelpers::infy_tab($spaces), $tabs);
    }

    /**
     * Generates new line char.
     *
     * @param int $count
     *
     * @return string
     */
    public static function infy_nl($count = 1)
    {
        return str_repeat(PHP_EOL, $count);
    }

    /**
     * Generates new line char.
     *
     * @param int $count
     * @param int $nls
     *
     * @return string
     */
    public static function infy_nls($count, $nls = 1)
    {
        return str_repeat(InfyOmHelpers::infy_nl($nls), $count);
    }

    /**
     * Generates new line char.
     *
     * @param int $lns
     * @param int $tabs
     *
     * @return string
     */
    public static function infy_nl_tab($lns = 1, $tabs = 1)
    {
        return InfyOmHelpers::infy_nls($lns) . InfyOmHelpers::infy_tabs($tabs);
    }

    /**
     * get path for template file.
     *
     * @param string $templateName
     * @param string $templateType
     *
     * @return string
     */
    public static function get_template_file_path($templateName, $templateType)
    {
        $templateName = str_replace('.', '/', $templateName);

        $templatesPath = config(
            'infyom.laravel_generator.path.templates_dir',
            base_path('resources/infyom/infyom-generator-templates/')
        );

        $path = $templatesPath . $templateName . '.stub';

        if (file_exists($path)) {
            return $path;
        }

        return base_path('vendor/infyomlabs/' . $templateType . '/templates/' . $templateName . '.stub');
    }

    /**
     * get template contents.
     *
     * @param string $templateName
     * @param string $templateType
     *
     * @return string
     */
    public static function get_template($templateName, $templateType)
    {
        $path = InfyOmHelpers::get_template_file_path($templateName, $templateType);

        return file_get_contents($path);
    }

    /**
     * fill template with variable values.
     *
     * @param array $variables
     * @param string $template
     *
     * @return string
     */
    public static function fill_template($variables, $template)
    {
        foreach ($variables as $variable => $value) {
            $template = str_replace($variable, $value, $template);
        }

        return $template;
    }

    /**
     * fill field template with variable values.
     *
     * @param array $variables
     * @param string $template
     * @param \InfyOm\Generator\Common\GeneratorField $field
     *
     * @return string
     */
    public static function fill_field_template($variables, $template, $field)
    {
        foreach ($variables as $variable => $key) {
            $template = str_replace($variable, $field->$key, $template);
        }

        return $template;
    }

    /**
     * fill template with field data.
     *
     * @param array $variables
     * @param array $fieldVariables
     * @param string $template
     * @param \InfyOm\Generator\Common\GeneratorField $field
     *
     * @return string
     */
    public static function fill_template_with_field_data($variables, $fieldVariables, $template, $field)
    {
        $template = InfyOmHelpers::fill_template($variables, $template);

        return InfyOmHelpers::fill_field_template($fieldVariables, $template, $field);
    }

    /**
     * generates model name from table name.
     *
     * @param string $tableName
     *
     * @return string
     */
    public static function model_name_from_table_name($tableName)
    {
        return ucfirst(camel_case(str_singular($tableName)));
    }
}
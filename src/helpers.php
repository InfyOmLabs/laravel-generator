<?php

use Illuminate\Support\Str;
use InfyOm\Generator\Common\FileSystem;
use InfyOm\Generator\Common\GeneratorField;

if (!function_exists('g_filesystem')) {
    /**
     * @return FileSystem
     */
    function g_filesystem()
    {
        return app(FileSystem::class);
    }
}

if (!function_exists('infy_tab')) {
    function infy_tab(int $spaces = 4): string
    {
        return str_repeat(' ', $spaces);
    }
}

if (!function_exists('infy_tabs')) {
    function infy_tabs(int $tabs, int $spaces = 4): string
    {
        return str_repeat(infy_tab($spaces), $tabs);
    }
}

if (!function_exists('infy_nl')) {
    function infy_nl(int $count = 1): string
    {
        return str_repeat(PHP_EOL, $count);
    }
}

if (!function_exists('infy_nls')) {
    function infy_nls(int $count, int $nls = 1): string
    {
        return str_repeat(infy_nl($nls), $count);
    }
}

if (!function_exists('infy_nl_tab')) {
    function infy_nl_tab(int $lns = 1, int $tabs = 1): string
    {
        return infy_nls($lns).infy_tabs($tabs);
    }
}

if (!function_exists('get_template_file_path')) {
    function get_template_file_path(string $templateName, string $templateType): string
    {
        $templateName = str_replace('.', '/', $templateName);

        $templatesPath = config(
            'laravel_generator.path.templates_dir',
            resource_path('infyom/infyom-generator-templates/')
        );

        $path = $templatesPath.$templateName.'.blade.php';

        if (file_exists($path)) {
            return $path;
        }

        return get_templates_package_path($templateType).'/templates/'.$templateName.'.blade.php';
    }
}

if (!function_exists('get_file_path')) {
    function get_file_path(string $templateName, string $templateType): string
    {
        $templateName = str_replace('.', '/', $templateName);

        $templatesPath = config(
            'laravel_generator.path.templates_dir',
            resource_path('infyom/infyom-generator-templates/')
        );

        $path = $templatesPath.$templateName.'.blade.php';

        if (file_exists($path)) {
            return $path;
        }

        return get_templates_package_path($templateType).'/'.$templateName.'.blade.php';
    }
}

if (!function_exists('get_templates_package_path')) {
    function get_templates_package_path(string $templateType): string
    {
        if (!str_contains($templateType, '/')) {
            $templateType = base_path('vendor/infyomlabs/').$templateType;
        }

        return $templateType;
    }
}

if (!function_exists('get_template')) {
    function get_template(string $templateName, string $templateType): string
    {
        $path = get_template_file_path($templateName, $templateType);

        return g_filesystem()->getFile($path);
    }
}

if (!function_exists('fill_template')) {
    function fill_template(array $variables, string $template): string
    {
        foreach ($variables as $variable => $value) {
            $template = str_replace($variable, $value, $template);
        }

        return $template;
    }
}

if (!function_exists('fill_field_template')) {
    function fill_field_template(array $variables, string $template, GeneratorField $field): string
    {
        foreach ($variables as $variable => $key) {
            $template = str_replace($variable, $field->$key, $template);
        }

        return $template;
    }
}

if (!function_exists('fill_template_with_field_data')) {
    function fill_template_with_field_data(array $variables, array $fieldVariables, string $template, GeneratorField $field): string
    {
        $template = fill_template($variables, $template);

        return fill_field_template($fieldVariables, $template, $field);
    }
}

if (!function_exists('fill_template_with_field_data_locale')) {
    function fill_template_with_field_data_locale(array $variables, array $fieldVariables, string $template, GeneratorField $field): string
    {
        $template = fill_template($variables, $template);
        $modelName = $variables['$MODEL_NAME_PLURAL_CAMEL$'];

        return fill_field_template_locale($fieldVariables, $template, $field, $modelName);
    }
}

if (!function_exists('fill_field_template_locale')) {
    function fill_field_template_locale(array $variables, string $template, GeneratorField $field, string $modelName): string
    {
        foreach ($variables as $variable => $key) {
            $value = $field->name;
            $template = str_replace($variable, "@lang('models/$modelName.fields.$value')", $template);
        }

        return $template;
    }
}

if (!function_exists('model_name_from_table_name')) {
    function model_name_from_table_name(string $tableName): string
    {
        return Str::ucfirst(Str::camel(Str::singular($tableName)));
    }
}

function createResourceRouteNames($name, $isScaffold = false)
{
    $result = [
        "'index' => '$name.index'",
        "'store' => '$name.store'",
        "'show' => '$name.show'",
        "'update' => '$name.update'",
        "'destroy' => '$name.destroy'",
    ];

    if ($isScaffold) {
        $result[] = "'create' => '$name.create'";
        $result[] = "'edit' => '$name.edit'";
    }

    return $result;
}

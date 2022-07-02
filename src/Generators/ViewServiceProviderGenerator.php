<?php

namespace InfyOm\Generator\Generators;

use Illuminate\Support\Facades\File;

class ViewServiceProviderGenerator extends BaseGenerator
{
    /**
     * Generate ViewServiceProvider.
     */
    public function generate()
    {
        $templateData = get_template_file_path('view_service_provider', 'laravel-generator');

        $destination = $this->config->paths->viewProvider;

        $fileName = basename($destination);

        if (File::exists($destination)) {
            return;
        }
        File::copy($templateData, $destination);

        $this->config->commandComment($fileName.' published');
        $this->config->commandInfo($fileName);
    }

    /**
     * @param string      $views
     * @param string      $variableName
     * @param string      $columns
     * @param string      $tableName
     * @param string|null $modelName
     */
    public function addViewVariables($views, $variableName, $columns, $tableName, $modelName = null)
    {
        if (!empty($modelName)) {
            $model = $modelName;
        } else {
            $model = model_name_from_table_name($tableName);
        }

        $this->config->addDynamicVariable('$COMPOSER_VIEWS$', $views);
        $this->config->addDynamicVariable('$COMPOSER_VIEW_VARIABLE$', $variableName);
        $this->config->addDynamicVariable(
            '$COMPOSER_VIEW_VARIABLE_VALUES$',
            $model."::pluck($columns)->toArray()"
        );

        $mainViewContent = $this->addViewComposer();
        $mainViewContent = $this->addNamespace($model, $mainViewContent);
        $this->addCustomProvider();

        g_filesystem()->createFile($this->config->paths->viewProvider, $mainViewContent);
        $this->config->commandComment('View service provider file updated.');
    }

    public function addViewComposer(): string
    {
        $mainViewContent = g_filesystem()->getFile($this->config->paths->viewProvider);
        $newViewStatement = get_template('scaffold.view_composer', 'laravel-generator');
        $newViewStatement = fill_template($this->config->dynamicVars, $newViewStatement);

        $newViewStatement = infy_nl().$newViewStatement;
        preg_match_all('/public function boot(.*)/', $mainViewContent, $matches);

        $totalMatches = count($matches[0]);
        $lastSeederStatement = $matches[0][$totalMatches - 1];

        $replacePosition = strpos($mainViewContent, $lastSeederStatement);

        return substr_replace(
            $mainViewContent,
            $newViewStatement,
            $replacePosition + strlen($lastSeederStatement) + 6,
            0
        );
    }

    public function addCustomProvider()
    {
        $configFile = base_path().'/config/app.php';
        $file = g_filesystem()->getFile($configFile);
        $searchFor = 'Illuminate\View\ViewServiceProvider::class,';
        $customProviders = strpos($file, $searchFor);

        $isExist = strpos($file, "App\Providers\ViewServiceProvider::class");
        if ($customProviders && !$isExist) {
            $newChanges = substr_replace(
                $file,
                infy_nl().infy_tab(8).'\App\Providers\ViewServiceProvider::class,',
                $customProviders + strlen($searchFor),
                0
            );
            g_filesystem()->createFile($configFile, $newChanges);
        }
    }

    public function addNamespace($model, $mainViewContent)
    {
        $newModelStatement = 'use '.$this->config->namespaces->model.'\\'.$model.';';
        $isNameSpaceExist = strpos($mainViewContent, $newModelStatement);
        $newModelStatement = infy_nl().$newModelStatement;
        if (!$isNameSpaceExist) {
            preg_match_all('/namespace(.*)/', $mainViewContent, $matches);
            $totalMatches = count($matches[0]);
            $nameSpaceStatement = $matches[0][$totalMatches - 1];
            $replacePosition = strpos($mainViewContent, $nameSpaceStatement);
            $mainViewContent = substr_replace(
                $mainViewContent,
                $newModelStatement,
                $replacePosition + strlen($nameSpaceStatement),
                0
            );
        }

        return $mainViewContent;
    }
}

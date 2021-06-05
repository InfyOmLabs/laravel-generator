<?php

namespace InfyOm\Generator\Generators;

use File;
use InfyOm\Generator\Common\CommandData;

/**
 * Class ViewServiceProviderGenerator.
 */
class ViewServiceProviderGenerator extends BaseGenerator
{
    private $commandData;

    /**
     * ViewServiceProvider constructor.
     *
     * @param CommandData $commandData
     */
    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
    }

    /**
     * Generate ViewServiceProvider.
     */
    public function generate()
    {
        $templateData = get_template_file_path('view_service_provider', 'laravel-generator');

        $destination = $this->commandData->config->pathViewProvider;

        $fileName = basename($this->commandData->config->pathViewProvider);

        if (File::exists($destination)) {
            return;
        }
        File::copy($templateData, $destination);

        $this->commandData->commandComment($fileName.' published');
        $this->commandData->commandInfo($fileName);
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

        $this->commandData->addDynamicVariable('$COMPOSER_VIEWS$', $views);
        $this->commandData->addDynamicVariable('$COMPOSER_VIEW_VARIABLE$', $variableName);
        $this->commandData->addDynamicVariable(
            '$COMPOSER_VIEW_VARIABLE_VALUES$',
            $model."::pluck($columns)->toArray()"
        );

        $mainViewContent = $this->addViewComposer();
        $mainViewContent = $this->addNamespace($model, $mainViewContent);
        $this->addCustomProvider();

        file_put_contents($this->commandData->config->pathViewProvider, $mainViewContent);
        $this->commandData->commandComment('View service provider file updated.');
    }

    public function addViewComposer()
    {
        $mainViewContent = file_get_contents($this->commandData->config->pathViewProvider);
        $newViewStatement = get_template('scaffold.view_composer', 'laravel-generator');
        $newViewStatement = fill_template($this->commandData->dynamicVars, $newViewStatement);

        $newViewStatement = infy_nl(1).$newViewStatement;
        preg_match_all('/public function boot(.*)/', $mainViewContent, $matches);

        $totalMatches = count($matches[0]);
        $lastSeederStatement = $matches[0][$totalMatches - 1];

        $replacePosition = strpos($mainViewContent, $lastSeederStatement);
        $mainViewContent = substr_replace(
            $mainViewContent,
            $newViewStatement,
            $replacePosition + strlen($lastSeederStatement) + 6,
            0
        );

        return $mainViewContent;
    }

    public function addCustomProvider()
    {
        $configFile = base_path().'/config/app.php';
        $file = file_get_contents($configFile);
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
            file_put_contents($configFile, $newChanges);
        }
    }

    public function addNamespace($model, $mainViewContent)
    {
        $newModelStatement = 'use '.$this->commandData->config->nsModel.'\\'.$model.';';
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

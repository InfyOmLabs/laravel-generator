<?php

namespace InfyOm\Generator\Generators;

use File;
use InfyOm\Generator\Common\CommandData;

/**
 * Class ViewServiceProviderGenerator
 */
class ViewServiceProviderGenerator extends BaseGenerator
{
    private $commandData;

    /**
     * ViewServiceProvider constructor.
     *
     * @param  CommandData  $commandData
     */
    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
    }

    /**
     * Generate ViewServiceProvider
     */
    public function generate()
    {
        $templateData = get_template_file_path('view_service_provider', 'laravel-generator');

        $destination = $this->commandData->config->pathViewProvider;

        $fileName = basename($this->commandData->config->pathViewProvider);

        File::copy($templateData, $destination);

        $this->commandData->commandComment($fileName.' published');
        $this->commandData->commandInfo($fileName);
    }

    /**
     * @param  string  $views
     * @param  string  $variableName
     * @param  string  $columns
     * @param  string  $tableName
     */
    public function addViewVariables($views, $variableName, $columns, $tableName)
    {
        $nsModel = $this->commandData->config->nsModel.'\\'.model_name_from_table_name($tableName);

        $mainSeederContent = file_get_contents($this->commandData->config->pathViewProvider);
        $newViewStatement = get_template('scaffold.view_composer', 'laravel-generator');
        $this->commandData->addDynamicVariable('$COMPOSER_VIEWS$', $views);
        $this->commandData->addDynamicVariable('$COMPOSER_VIEW_VARIABLE$', $variableName);
        $this->commandData->addDynamicVariable(
            '$COMPOSER_VIEW_VARIABLE_VALUES$', $nsModel."::pluck($columns)->toArray()"
        );

        $newViewStatement = fill_template($this->commandData->dynamicVars, $newViewStatement);

        $newViewStatement = infy_tabs(2).$newViewStatement.infy_nl();

        preg_match_all('/function boot(.*)/', $mainSeederContent, $matches);

        $totalMatches = count($matches[0]);
        $lastSeederStatement = $matches[0][$totalMatches - 1];

        $replacePosition = strpos($mainSeederContent, $lastSeederStatement);

        $mainSeederContent = substr_replace(
            $mainSeederContent, $newViewStatement, $replacePosition + strlen($lastSeederStatement) + 6, 0
        );

        file_put_contents($this->commandData->config->pathViewProvider, $mainSeederContent);
        $this->commandData->commandComment('View service provider file updated.');
    }
}
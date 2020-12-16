<?php

namespace InfyOm\Generator\Generators\Scaffold;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\FileUtil;

/**
 * Class JQueryDatatableAssetsGenerator.
 */
class JQueryDatatableAssetsGenerator extends BaseGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    /** @var string */
    private $fileName;

    private $config;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathAssets.'js/';
        $this->config = $this->commandData->config;
        $this->fileName = $this->config->tableName.'.js';
    }

    public function generate()
    {
        $this->generateJquery();
    }

    public function generateJquery()
    {
        $templateName = 'jquery';

        if ($this->commandData->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }

        $columnsCount = 0;

        $fields = '';
        foreach ($this->commandData->fields as $field) {
            if (in_array($field->name, ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }

            $fields .= "{
            data: '$field->name',
            name: '$field->name'
        },";

            $columnsCount++;
        }

        // Publish Datatable JS file
        $templateData = get_template('scaffold.'.$templateName, 'laravel-generator');
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);
        $templateData = str_replace('$ACTION_COLUMN_COUNT$', $columnsCount, $templateData);
        $templateData = str_replace('$JQUERY_FIELDS$', $fields, $templateData);

        $path = $this->path.$this->config->tableName.'/';
        if (!file_exists($path)) {
            FileUtil::createDirectoryIfNotExist($path);
        }
        file_put_contents($path.$this->fileName, $templateData);
        $this->commandData->commandComment("\n".$this->config->tableName.' assets added.');

        // Publish JS Rendere Template
        $templateName = 'js_renderer_template';
        $templateData = get_template('scaffold.'.$templateName, 'laravel-generator');
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $path = $this->config->pathViews.'templates/';
        if (!file_exists($path)) {
            FileUtil::createDirectoryIfNotExist($path);
        }

        file_put_contents($path.'templates.php', $templateData);
        $this->commandData->commandComment("\n".'JS Render Templates added.');

        // Publish Webpack mix lines
        $webpackMixContents = file_get_contents(base_path('webpack.mix.js'));
        $templateName = 'webpack_mix_js';
        $templateData = get_template('scaffold.'.$templateName, 'laravel-generator');
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);
        $webpackMixContents .= "\n\n".$templateData;

        file_put_contents(base_path('webpack.mix.js'), $webpackMixContents);
        $this->commandData->commandComment("\n".$this->commandData->config->mCamelPlural.' webpack.mix.js updated.');
    }
}

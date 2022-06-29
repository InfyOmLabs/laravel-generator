<?php

namespace InfyOm\Generator\Generators\Scaffold;

use Illuminate\Support\Str;
use InfyOm\Generator\Common\GeneratorConfig;
use InfyOm\Generator\Generators\BaseGenerator;

class MenuGenerator extends BaseGenerator
{
    private GeneratorConfig $config;

    private string $path;

    private string $templateType;

    private string $menuContents;

    private string $menuTemplate;

    public function __construct(GeneratorConfig $config)
    {
        $this->config = $config;
        $this->path = config('laravel_generator.path.menu_file', resource_path('views/layouts/menu.blade.php'));
        $this->templateType = config('laravel_generator.templates', 'adminlte-templates');

        $this->menuContents = file_get_contents($this->path);

        $templateName = 'menu_template';

        if ($this->config->isLocalizedTemplates()) {
            $templateName .= '_locale';
        }

        $this->menuTemplate = get_template('scaffold.layouts.'.$templateName, $this->templateType);

        $this->menuTemplate = fill_template($this->config->dynamicVars, $this->menuTemplate);
    }

    public function generate()
    {
        $this->menuContents .= $this->menuTemplate.infy_nl();
        $existingMenuContents = file_get_contents($this->path);
        // adminlte uses <p> tab and coreui+stisla uses <span> tag for menu
        if (Str::contains($existingMenuContents, '<p>'.$this->config->modelNames->humanPlural.'</p>') or
            Str::contains($existingMenuContents, '<span>'.$this->config->modelNames->humanPlural.'</span>')) {
            $this->config->commandInfo('Menu '.$this->config->modelNames->humanPlural.' is already exists, Skipping Adjustment.');

            return;
        }

        file_put_contents($this->path, $this->menuContents);
        $this->config->commandComment("\n".$this->config->modelNames->dashedPlural.' menu added.');
    }

    public function rollback()
    {
        if (Str::contains($this->menuContents, $this->menuTemplate)) {
            file_put_contents($this->path, str_replace($this->menuTemplate, '', $this->menuContents));
            $this->config->commandComment('menu deleted');
        }
    }
}

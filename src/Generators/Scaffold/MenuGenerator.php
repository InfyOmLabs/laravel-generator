<?php

namespace InfyOm\Generator\Generators\Scaffold;

use Illuminate\Support\Str;
use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\BaseGenerator;

class MenuGenerator extends BaseGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    /** @var string */
    private $templateType;

    /** @var string */
    private $menuContents;

    /** @var string */
    private $menuTemplate;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = config(
            'infyom.laravel_generator.path.menu_views',
            base_path('resources/views/'
            )
        ).$commandData->getAddOn('menu.menu_file');
        $this->templateType = config('infyom.laravel_generator.templates', 'adminlte-templates');

        if (!is_dir(dirname($this->path))) {
            mkdir(dirname($this->path), 755, true);
        }
        if (!file_exists($this->path)) {
            touch($this->path);
        }
        $this->menuContents = file_get_contents($this->path);

        $this->menuTemplate = get_template('scaffold.layouts.menu_template', $this->templateType);

        $this->menuTemplate = fill_template($this->commandData->dynamicVars, $this->menuTemplate);
    }

    public function generate()
    {
        if (strpos($this->menuContents, $this->menuTemplate.infy_nl()) === false) {
            $this->menuContents .= $this->menuTemplate.infy_nl();

            file_put_contents($this->path, $this->menuContents);
        }

        $this->commandData->commandComment("\n".$this->commandData->config->mCamelPlural.' menu added.');
    }

    public function rollback()
    {
        if (Str::contains($this->menuContents, $this->menuTemplate)) {
            file_put_contents($this->path, str_replace($this->menuTemplate, '', $this->menuContents));
            $this->commandData->commandComment('menu deleted');
        }
    }
}

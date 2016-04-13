<?php

namespace InfyOm\Generator\Generators\Scaffold;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Utils\TemplateUtil;

class MenuGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    /** @var string */
    private $templateType;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = config(
            'infyom.laravel_generator.path.views',
            base_path('resources/views/'
            )
        ). $commandData->getAddOn('menu.menu_file');
        $this->templateType = config('infyom.laravel_generator.path.templates', 'core-templates');
    }

    public function generate()
    {
        $menuContents = file_get_contents($this->path);

        $menuTemplate = TemplateUtil::getTemplate('scaffold.layouts.menu_template', $this->templateType);

        $menuTemplate = TemplateUtil::fillTemplate($this->commandData->dynamicVars, $menuTemplate);

        $menuContents .= $menuTemplate.infy_nl();

        file_put_contents($this->path, $menuContents);
        $this->commandData->commandComment("\n".$this->commandData->config->mCamelPlural.' menu added.');
    }
}

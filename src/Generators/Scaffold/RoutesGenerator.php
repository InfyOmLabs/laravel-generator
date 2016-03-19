<?php

namespace InfyOm\Generator\Generators\Scaffold;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Utils\TemplateUtil;

class RoutesGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathRoutes;
    }

    public function generate()
    {
        $routeContents = file_get_contents($this->path);

        $routesTemplate = TemplateUtil::getTemplate('scaffold.routes.routes', 'laravel-generator');

        $routesTemplate = TemplateUtil::fillTemplate($this->commandData->dynamicVars, $routesTemplate);

        $routeContents .= "\n\n".$routesTemplate;

        file_put_contents($this->path, $routeContents);
        $this->commandData->commandComment("\n".$this->commandData->config->mCamelPlural.' routes added.');
    }
}

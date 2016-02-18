<?php

namespace InfyOm\Generator\Generators\Scaffold;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Utils\TemplateUtil;

class RoutesGenerator
{
    /** @var  CommandData */
    private $commandData;

    /** @var string */
    private $path;

    public function __construct($commandData)
    {
        $this->commandData = $commandData;
        $this->path = config('infyom.laravel_generator.path.routes', app_path('Http/routes.php'));
    }

    public function generate()
    {
        $routeContents = file_get_contents($this->path);

        $routesTemplate = TemplateUtil::getTemplate('scaffold.routes.routes', 'laravel-generator');

        $routesTemplate = TemplateUtil::fillTemplate($this->commandData->dynamicVars, $routesTemplate);

        $routeContents .= "\n\n".$routesTemplate;

        file_put_contents($this->path, $routeContents);
        $this->commandData->commandComment("\n".$this->commandData->modelNames['camelPlural'].' routes added.');
    }
}

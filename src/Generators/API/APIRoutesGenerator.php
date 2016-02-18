<?php

namespace InfyOm\Generator\Generators\API;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Utils\TemplateUtil;

class APIRoutesGenerator
{
    /** @var  CommandData */
    private $commandData;

    /** @var string */
    private $path;

    public function __construct($commandData)
    {
        $this->commandData = $commandData;
        $this->path = config('infyom.laravel_generator.path.api_routes', app_path('Http/api_routes.php'));
    }

    public function generate()
    {
        $routeContents = file_get_contents($this->path);

        $routesTemplate = TemplateUtil::getTemplate('api.routes.routes', 'laravel-generator');

        $routesTemplate = TemplateUtil::fillTemplate($this->commandData->dynamicVars, $routesTemplate);

        $routeContents .= "\n\n".$routesTemplate;

        file_put_contents($this->path, $routeContents);
        $this->commandData->commandComment("\n".$this->commandData->modelNames['camelPlural'].' routes added.');
    }
}

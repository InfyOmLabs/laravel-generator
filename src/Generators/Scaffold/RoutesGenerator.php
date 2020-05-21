<?php

namespace InfyOm\Generator\Generators\Scaffold;

use Illuminate\Support\Str;
use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\FileUtil;

class RoutesGenerator extends BaseGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $pathRoutes;

    /** @var string */
    private $pathMigrations;

    /** @var string */
    private $routeContents;

    /** @var string */
    private $routesTemplate;

    /** @var string */
    private $fileNameMigration;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;

        $this->pathRoutes = $commandData->config->pathRoutes;
        $this->pathMigrations = config('infyom.laravel_generator.path.migration', database_path('migrations/'));

        $this->routeContents = file_get_contents($this->pathRoutes);
        if (!empty($this->commandData->config->prefixes['route'])) {
            $this->routesTemplate = get_template('scaffold.routes.prefix_routes', 'laravel-generator');
        } else {
            $this->routesTemplate = get_template('scaffold.routes.routes', 'laravel-generator');
        }
        $this->routesTemplate = fill_template($this->commandData->dynamicVars, $this->routesTemplate);

        $modelNamePluralSnake = $this->commandData->dynamicVars['$MODEL_NAME_PLURAL_SNAKE$'];
        $this->fileNameMigration = date('Y_m_d_His').'_add_permissions_page_role_to_'.strtolower($modelNamePluralSnake).'.php';
    }

    public function generate()
    {
        $this->generateRoutes();
        $this->generatePermissions();
    }

    public function generateRoutes() {
        $this->routeContents .= "\n\n".$this->routesTemplate;
        $existingRouteContents = file_get_contents($this->pathRoutes);
        if (Str::contains($existingRouteContents, "Route::resource('".$this->commandData->config->mSnakePlural."',")) {
            $this->commandData->commandObj->info('Route '.$this->commandData->config->mPlural.' is already exists, Skipping Adjustment.');

            return;
        }

        file_put_contents($this->pathRoutes, $this->routeContents);
        $this->commandData->commandComment("\n".$this->commandData->config->mCamelPlural.' routes added.');
    }

    public function generatePermissions() {
        $templateData = get_template('scaffold.permissions', 'laravel-generator');
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->pathMigrations, $this->fileNameMigration, $templateData);
        $this->commandData->commandComment("\n".$this->commandData->config->mCamelPlural.' permissions added.');
    }

    public function rollback()
    {
        if (Str::contains($this->routeContents, $this->routesTemplate)) {
            $this->routeContents = str_replace($this->routesTemplate, '', $this->routeContents);
            file_put_contents($this->pathRoutes, $this->routeContents);
            $this->commandData->commandComment('scaffold routes deleted');
        }

        if ($this->rollbackFile($this->pathMigrations, $this->fileNameMigration)) {
            $this->commandData->commandComment('Permission file deleted: '.$this->fileNameMigration);
        }
    }
}

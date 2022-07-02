<?php /** @noinspection PhpUnusedAliasInspection */

namespace InfyOm\Generator\Generators\Scaffold;

use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Generators\ModelGenerator;

class RequestGenerator extends BaseGenerator
{
    private string $createFileName;

    private string $updateFileName;

    public function __construct()
    {
        parent::__construct();

        $this->path = $this->config->paths->request;
        $this->createFileName = 'Create'.$this->config->modelNames->name.'Request.php';
        $this->updateFileName = 'Update'.$this->config->modelNames->name.'Request.php';
    }

    public function generate()
    {
        $this->generateCreateRequest();
        $this->generateUpdateRequest();
    }

    private function generateCreateRequest()
    {
        $templateData = get_template('scaffold.request.create_request', 'laravel-generator');

        $templateData = fill_template($this->config->dynamicVars, $templateData);

        g_filesystem()->createFile($this->path, $this->createFileName, $templateData);

        $this->config->commandComment(PHP_EOL.'Create Request created: ');
        $this->config->commandInfo($this->createFileName);
    }

    private function generateUpdateRequest()
    {
        $modelGenerator = new ModelGenerator();
        $rules = $modelGenerator->generateUniqueRules();
        $this->config->addDynamicVariable('$UNIQUE_RULES$', $rules);

        $templateData = get_template('scaffold.request.update_request', 'laravel-generator');

        $templateData = fill_template($this->config->dynamicVars, $templateData);

        g_filesystem()->createFile($this->path, $this->updateFileName, $templateData);

        $this->config->commandComment(PHP_EOL.'Update Request created: ');
        $this->config->commandInfo($this->updateFileName);
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->createFileName)) {
            $this->config->commandComment('Create Request file deleted: '.$this->createFileName);
        }

        if ($this->rollbackFile($this->path, $this->updateFileName)) {
            $this->config->commandComment('Update Request file deleted: '.$this->updateFileName);
        }
    }
}

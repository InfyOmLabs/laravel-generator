<?php /** @noinspection PhpUnusedAliasInspection */

namespace InfyOm\Generator\Generators;

class RepositoryGenerator extends BaseGenerator
{
    private string $fileName;

    public function __construct()
    {
        parent::__construct();

        $this->path = $this->config->paths->repository;
        $this->fileName = $this->config->modelNames->name.'Repository.php';
    }

    public function generate()
    {
        $templateData = get_template('repository', 'laravel-generator');

        $templateData = fill_template($this->config->dynamicVars, $templateData);

        $searchables = [];

        foreach ($this->config->fields as $field) {
            if ($field->isSearchable) {
                $searchables[] = "'".$field->name."'";
            }
        }

        $templateData = str_replace('$FIELDS$', implode(','.infy_nl_tab(1, 2), $searchables), $templateData);

        $docsTemplate = get_template('docs.repository', 'laravel-generator');
        $docsTemplate = fill_template($this->config->dynamicVars, $docsTemplate);
        $docsTemplate = str_replace('$GENERATE_DATE$', date('F j, Y, g:i a T'), $docsTemplate);

        $templateData = str_replace('$DOCS$', $docsTemplate, $templateData);

        g_filesystem()->createFile($this->path, $this->fileName, $templateData);

        $this->config->commandComment(PHP_EOL.'Repository created: ');
        $this->config->commandInfo($this->fileName);
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->config->commandComment('Repository file deleted: '.$this->fileName);
        }
    }
}

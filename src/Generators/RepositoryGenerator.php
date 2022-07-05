<?php

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

    public function variables(): array
    {
        return [
            'fieldSearchable' => $this->getSearchableFields(),
        ];
    }

    public function generate()
    {
        $templateData = view('laravel-generator::repository.repository', $this->variables())->render();

        g_filesystem()->createFile($this->path.$this->fileName, $templateData);

        $this->config->commandComment(infy_nl().'Repository created: ');
        $this->config->commandInfo($this->fileName);
    }

    protected function getSearchableFields()
    {
        $searchables = [];

        foreach ($this->config->fields as $field) {
            if ($field->isSearchable) {
                $searchables[] = "'".$field->name."'";
            }
        }

        return implode(','.infy_nl_tab(1, 2), $searchables);
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->config->commandComment('Repository file deleted: '.$this->fileName);
        }
    }
}

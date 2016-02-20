<?php

namespace InfyOm\Generator\Generators;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Utils\FileUtil;
use InfyOm\Generator\Utils\TemplateUtil;

class ModelGenerator
{
    /** @var  CommandData */
    private $commandData;

    /** @var string */
    private $path;

    public function __construct($commandData)
    {
        $this->commandData = $commandData;
        $this->path = config('infyom.laravel_generator.path.model', app_path('Models/'));
    }

    public function generate()
    {
        $templateData = TemplateUtil::getTemplate('model', 'laravel-generator');

        $templateData = $this->fillTemplate($templateData);

        $fileName = $this->commandData->modelName.'.php';

        FileUtil::createFile($this->path, $fileName, $templateData);

        $this->commandData->commandComment("\nModel created: ");
        $this->commandData->commandInfo($fileName);
    }

    private function fillTemplate($templateData)
    {
        $templateData = TemplateUtil::fillTemplate($this->commandData->dynamicVars, $templateData);

        $templateData = $this->fillSoftDeletes($templateData);

        $fillables = [];

        foreach ($this->commandData->inputFields as $field) {
            if ($field['fillable']) {
                $fillables[] = '"'.$field['fieldName'].'"';
            }
        }

        $templateData = $this->fillDocs($templateData);

        if ($this->commandData->getOption('primary')) {
            $primary = "protected \$primaryKey = '".$this->commandData->getOption('primary')."';\n";
        } else {
            $primary = '';
        }

        $templateData = str_replace('$PRIMARY$', $primary, $templateData);

        $templateData = str_replace('$FIELDS$', implode(",\n\t\t", $fillables), $templateData);

        $templateData = str_replace('$RULES$', implode(",\n\t\t", $this->generateRules()), $templateData);

        $templateData = str_replace('$CAST$', implode(",\n\t\t", $this->generateCasts()), $templateData);

        return $templateData;
    }

    private function fillSoftDeletes($templateData)
    {
        if (!$this->commandData->getOption('softDelete')) {
            $templateData = str_replace('$SOFT_DELETE_IMPORT$', '', $templateData);
            $templateData = str_replace('$SOFT_DELETE$', '', $templateData);
            $templateData = str_replace('$SOFT_DELETE_DATES$', '', $templateData);
        } else {
            $templateData = str_replace('$SOFT_DELETE_IMPORT$', "use Illuminate\\Database\\Eloquent\\SoftDeletes;\n",
                $templateData);
            $templateData = str_replace('$SOFT_DELETE$', "use SoftDeletes;\n", $templateData);
            $templateData = str_replace('$SOFT_DELETE_DATES$', "\n\tprotected \$dates = ['deleted_at'];\n",
                $templateData);
        }

        return $templateData;
    }

    private function fillDocs($templateData)
    {
        if ($this->commandData->getAddOn('swagger')) {
            $templateData = $this->generateSwagger($templateData);
        } else {
            $docsTemplate = TemplateUtil::getTemplate('docs.model', 'laravel-generator');
            $docsTemplate = TemplateUtil::fillTemplate($this->commandData->dynamicVars, $docsTemplate);
            $templateData = str_replace('$DOCS$', $docsTemplate, $templateData);
        }

        return $templateData;
    }

    public function generateSwagger($templateData)
    {
        $fieldTypes = SwaggerGenerator::generateTypes($this->commandData->inputFields);

        $template = TemplateUtil::getTemplate('model.model', 'swagger-generator');

        $template = TemplateUtil::fillTemplate($this->commandData->dynamicVars, $template);

        $template = str_replace('$REQUIRED_FIELDS$', implode(', ', $this->generateRequiredFields()), $template);

        $propertyTemplate = TemplateUtil::getTemplate('model.property', 'swagger-generator');

        $properties = SwaggerGenerator::preparePropertyFields($propertyTemplate, $fieldTypes);

        $template = str_replace('$PROPERTIES$', implode(",\n", $properties), $template);

        $templateData = str_replace('$DOCS$', $template, $templateData);

        return $templateData;
    }

    private function generateRequiredFields()
    {
        $requiredFields = [];

        foreach ($this->commandData->inputFields as $field) {
            if (!empty($field['validations'])) {
                if (str_contains($field['validations'], 'required')) {
                    $requiredFields[] = $field['fieldName'];
                }
            }
        }

        return $requiredFields;
    }

    private function generateRules()
    {
        $rules = [];

        foreach ($this->commandData->inputFields as $field) {
            if (!empty($field['validations'])) {
                $rule = '"'.$field['fieldName'].'" => "'.$field['validations'].'"';
                $rules[] = $rule;
            }
        }

        return $rules;
    }

    public function generateCasts()
    {
        $casts = [];

        foreach ($this->commandData->inputFields as $field) {
            switch ($field['fieldType']) {
                case 'integer':
                    $rule = '"'.$field['fieldName'].'" => "integer"';
                    break;
                case 'double':
                    $rule = '"'.$field['fieldName'].'" => "double"';
                    break;
                case 'float':
                    $rule = '"'.$field['fieldName'].'" => "float"';
                    break;
                case 'boolean':
                    $rule = '"'.$field['fieldName'].'" => "boolean"';
                    break;
                case 'enum':
                case 'string':
                case 'char':
                case 'text':
                    $rule = '"'.$field['fieldName'].'" => "string"';
                    break;
                default:
                    $rule = '';
                    break;
            }

            if (!empty($rule)) {
                $casts[] = $rule;
            }
        }

        return $casts;
    }
}

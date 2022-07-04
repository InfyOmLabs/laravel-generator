<?php

namespace InfyOm\Generator\Generators;

use Illuminate\Support\Str;
use InfyOm\Generator\Common\GeneratorFieldRelation;
use InfyOm\Generator\Utils\TableFieldsGenerator;

class ModelGenerator extends BaseGenerator
{
    /**
     * Fields not included in the generator by default.
     */
    protected array $excluded_fields = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    private string $fileName;

    public function __construct()
    {
        parent::__construct();

        $this->path = $this->config->paths->model;
        $this->fileName = $this->config->modelNames->name.'.php';
    }

    public function generate()
    {
        $templateData = view('laravel-generator::model.model', $this->variables())->render();

        g_filesystem()->createFile($this->path.$this->fileName, $templateData);

        $this->config->commandComment(PHP_EOL.'Model created: ');
        $this->config->commandInfo($this->fileName);
    }

    public function variables(): array
    {
        return [
            'fillables' => implode(','.infy_nl_tab(1, 2), $this->generateFillables()),
            'casts' => implode(','.infy_nl_tab(1, 2), $this->generateCasts()),
            'rules' => implode(','.infy_nl_tab(1, 2), $this->generateRules()),
            'docs' => $this->fillDocs(),
            'customPrimaryKey' => $this->customPrimaryKey(),
            'customCreatedAt' => $this->customCreatedAt(),
            'customUpdatedAt' => $this->customUpdatedAt(),
            'customSoftDelete' => $this->customSoftDelete(),
            'relations' => $this->generateRelations(),
            'timestamps' => config('laravel_generator.timestamps.enabled', true),
        ];
    }

    protected function customPrimaryKey()
    {
        $primary = $this->config->getOption('primary');

        if (!$primary) {
            return null;
        }

        if ($primary === 'id') {
            return null;
        }

        return $primary;
    }

    protected function customSoftDelete()
    {
        $deletedAt = config('laravel_generator.timestamps.deleted_at', 'deleted_at');

        if ($deletedAt === 'deleted_at') {
            return null;
        }

        return $deletedAt;
    }

    protected function customCreatedAt()
    {
        $createdAt = config('laravel_generator.timestamps.created_at', 'created_at');

        if ($createdAt === 'created_at') {
            return null;
        }

        return $createdAt;
    }

    protected function customUpdatedAt()
    {
        $updatedAt = config('laravel_generator.timestamps.updated_at', 'updated_at');

        if ($updatedAt === 'updated_at') {
            return null;
        }

        return $updatedAt;
    }

    protected function generateFillables(): array
    {
        $fillables = [];
        if (isset($this->config->fields) && !empty($this->config->fields)) {
            foreach ($this->config->fields as $field) {
                if ($field->isFillable) {
                    $fillables[] = "'".$field->name."'";
                }
            }
        }

        return $fillables;
    }

    private function fillDocs(): string
    {
        if (!$this->config->addons->swagger) {
            return '';
        }

        $templateData = $this->generateSwagger('');

        $docsTemplate = get_template('docs.model', 'laravel-generator');
        $docsTemplate = fill_template($this->config->dynamicVars, $docsTemplate);

        $fillables = '';
        $fieldsArr = [];
        $count = 1;
        if (isset($this->config->relations) && !empty($this->config->relations)) {
            foreach ($this->config->relations as $relation) {
                $field = $relationText = (isset($relation->inputs[0])) ? $relation->inputs[0] : null;
                if (in_array($field, $fieldsArr)) {
                    $relationText = $relationText.'_'.$count;
                    $count++;
                }

                $fillables .= ' * @property '.$this->getPHPDocType($relation->type, $relation, $relationText).PHP_EOL;
                $fieldsArr[] = $field;
            }
        }

        if (isset($this->config->fields) && !empty($this->config->fields)) {
            foreach ($this->config->fields as $field) {
                if ($field->isFillable) {
                    $fillables .= ' * @property '.$this->getPHPDocType($field->fieldType).' $'.$field->name.PHP_EOL;
                }
            }
        }

        $docsTemplate = str_replace('$GENERATE_DATE$', date('F j, Y, g:i a T'), $docsTemplate);
        $docsTemplate = str_replace('$PHPDOC$', $fillables, $docsTemplate);

        return str_replace('$DOCS$', $docsTemplate, $templateData);
    }

    private function getPHPDocType(string $dbType, GeneratorFieldRelation $relation = null, string $relationText = null): string
    {
        $relationText = (!empty($relationText)) ? $relationText : null;
        $modelNamespace = $this->config->namespaces->model;

        switch ($dbType) {
            case 'datetime':
                return 'string|\Carbon\Carbon';
            case '1t1':
                return '\\'.$modelNamespace.'\\'.$relation->inputs[0].' $'.Str::camel($relationText);
            case 'mt1':
                if (isset($relation->inputs[1])) {
                    $relationName = str_replace('_id', '', strtolower($relation->inputs[1]));
                } else {
                    $relationName = $relationText;
                }

                return '\\'.$modelNamespace.'\\'.$relation->inputs[0].' $'.Str::camel($relationName);
            case '1tm':
            case 'mtm':
            case 'hmt':
                return '\Illuminate\Database\Eloquent\Collection $'.Str::camel(Str::plural($relationText));
            default:
                $fieldData = SwaggerGenerator::getFieldType($dbType);
                if (!empty($fieldData['fieldType'])) {
                    return $fieldData['fieldType'];
                }

                return $dbType;
        }
    }

    public function generateSwagger($templateData): string
    {
        $fieldTypes = SwaggerGenerator::generateTypes($this->config->fields);

        $template = get_template('model_docs.model', 'swagger-generator');

        $template = fill_template($this->config->dynamicVars, $template);

        $template = str_replace(
            '$REQUIRED_FIELDS$',
            '"'.implode('"'.', '.'"', $this->generateRequiredFields()).'"',
            $template
        );

        $propertyTemplate = get_template('model_docs.property', 'swagger-generator');

        $properties = SwaggerGenerator::preparePropertyFields($propertyTemplate, $fieldTypes);

        $template = str_replace('$PROPERTIES$', implode(",\n", $properties), $template);

        return str_replace('$DOCS$', $template, $templateData);
    }

    private function generateRequiredFields(): array
    {
        $requiredFields = [];

        if (isset($this->config->fields) && !empty($this->config->fields)) {
            foreach ($this->config->fields as $field) {
                if (!empty($field->validations)) {
                    if (Str::contains($field->validations, 'required')) {
                        $requiredFields[] = $field->name;
                    }
                }
            }
        }

        return $requiredFields;
    }

    private function generateRules(): array
    {
        $dont_require_fields = config('laravel_generator.options.hidden_fields', [])
                + config('laravel_generator.options.excluded_fields', $this->excluded_fields);

        $rules = [];

        foreach ($this->config->fields as $field) {
            if (!$field->isPrimary && !in_array($field->name, $dont_require_fields)) {
                if ($field->isNotNull && empty($field->validations)) {
                    $field->validations = 'required';
                }

                /**
                 * Generate some sane defaults based on the field type if we
                 * are generating from a database table.
                 */
                if ($this->config->getOption('fromTable')) {
                    $rule = empty($field->validations) ? [] : explode('|', $field->validations);

                    if (!$field->isNotNull) {
                        $rule[] = 'nullable';
                    }

                    switch ($field->fieldType) {
                        case 'integer':
                            $rule[] = 'integer';
                            break;
                        case 'boolean':
                            $rule[] = 'boolean';
                            break;
                        case 'float':
                        case 'double':
                        case 'decimal':
                            $rule[] = 'numeric';
                            break;
                        case 'string':
                            $rule[] = 'string';

                            // Enforce a maximum string length if possible.
                            foreach (explode(':', $field->dbInput) as $key => $value) {
                                if (preg_match('/string,(\d+)/', $value, $matches)) {
                                    $rule[] = 'max:'.$matches[1];
                                }
                            }
                            break;
                        case 'text':
                            $rule[] = 'string';
                            break;
                    }

                    $field->validations = implode('|', $rule);
                }
            }

            if (!empty($field->validations)) {
                if (Str::contains($field->validations, 'unique:')) {
                    $rule = explode('|', $field->validations);
                    // move unique rule to last
                    usort($rule, function ($record) {
                        return (Str::contains($record, 'unique:')) ? 1 : 0;
                    });
                    $field->validations = implode('|', $rule);
                }
                $rule = "'".$field->name."' => '".$field->validations."'";
                $rules[] = $rule;
            }
        }

        return $rules;
    }

    public function generateUniqueRules(): string
    {
        $tableNameSingular = Str::singular($this->config->tableName);
        $uniqueRules = '';
        foreach ($this->generateRules() as $rule) {
            if (Str::contains($rule, 'unique:')) {
                $rule = explode('=>', $rule);
                $string = '$rules['.trim($rule[0]).'].","';

                $uniqueRules .= '$rules['.trim($rule[0]).'] = '.$string.'.$this->route("'.$tableNameSingular.'");';
            }
        }

        return $uniqueRules;
    }

    public function generateCasts(): array
    {
        $casts = [];

        $timestamps = TableFieldsGenerator::getTimestampFieldNames();

        foreach ($this->config->fields as $field) {
            if (in_array($field->name, $timestamps)) {
                continue;
            }

            $rule = "'".$field->name."' => ";

            switch (strtolower($field->fieldType)) {
                case 'integer':
                case 'increments':
                case 'smallinteger':
                case 'long':
                case 'biginteger':
                    $rule .= "'integer'";
                    break;
                case 'double':
                    $rule .= "'double'";
                    break;
                case 'decimal':
                    $rule .= sprintf("'decimal:%d'", $field->numberDecimalPoints);
                    break;
                case 'float':
                    $rule .= "'float'";
                    break;
                case 'boolean':
                    $rule .= "'boolean'";
                    break;
                case 'datetime':
                case 'datetimetz':
                    $rule .= "'datetime'";
                    break;
                case 'date':
                    $rule .= "'date'";
                    break;
                case 'enum':
                case 'string':
                case 'char':
                case 'text':
                    $rule .= "'string'";
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

    private function generateRelations(): string
    {
        $relations = [];

        $count = 1;
        $fieldsArr = [];
        if (isset($this->config->relations) && !empty($this->config->relations)) {
            foreach ($this->config->relations as $relation) {
                $field = (isset($relation->inputs[0])) ? $relation->inputs[0] : null;

                $relationShipText = $field;
                if (in_array($field, $fieldsArr)) {
                    $relationShipText = $relationShipText.'_'.$count;
                    $count++;
                }

                $relationText = $relation->getRelationFunctionText($relationShipText);
                if (!empty($relationText)) {
                    $fieldsArr[] = $field;
                    $relations[] = $relationText;
                }
            }
        }

        return implode(infy_nl_tab(), $relations);
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->config->commandComment('Model file deleted: '.$this->fileName);
        }
    }
}

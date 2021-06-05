<?php

namespace InfyOm\Generator\Generators;

use Illuminate\Support\Str;
use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Common\GeneratorFieldRelation;
use InfyOm\Generator\Utils\FileUtil;
use InfyOm\Generator\Utils\TableFieldsGenerator;

class ModelGenerator extends BaseGenerator
{
    /**
     * Fields not included in the generator by default.
     *
     * @var array
     */
    protected $excluded_fields = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;
    private $fileName;
    private $table;

    /**
     * ModelGenerator constructor.
     *
     * @param CommandData $commandData
     */
    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathModel;
        $this->fileName = $this->commandData->modelName.'.php';
        $this->table = $this->commandData->dynamicVars['$TABLE_NAME$'];
    }

    public function generate()
    {
        $templateData = get_template('model.model', 'laravel-generator');

        $templateData = $this->fillTemplate($templateData);

        FileUtil::createFile($this->path, $this->fileName, $templateData);

        $this->commandData->commandComment("\nModel created: ");
        $this->commandData->commandInfo($this->fileName);
    }

    private function fillTemplate($templateData)
    {
        $rules = $this->generateRules();
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $templateData = $this->fillSoftDeletes($templateData);

        $templateData = $this->fillHasFactory($templateData);

        $fillables = [];
        $primaryKey = 'id';

        foreach ($this->commandData->fields as $field) {
            if ($field->isFillable) {
                $fillables[] = "'".$field->name."'";
            }
            if ($field->isPrimary) {
                $primaryKey = $field->name;
            }
        }

        $templateData = $this->fillDocs($templateData);

        $templateData = $this->fillTimestamps($templateData);

        if ($this->commandData->getOption('primary')) {
            $primary = infy_tab()."protected \$primaryKey = '".$this->commandData->getOption('primary')."';\n";
        } else {
            $primary = '';
            if ($this->commandData->getOption('fieldsFile') && $primaryKey != 'id') {
                $primary = infy_tab()."protected \$primaryKey = '".$primaryKey."';\n";
            }
        }

        $templateData = str_replace('$PRIMARY$', $primary, $templateData);

        $templateData = str_replace('$FIELDS$', implode(','.infy_nl_tab(1, 2), $fillables), $templateData);

        $templateData = str_replace('$RULES$', implode(','.infy_nl_tab(1, 2), $rules), $templateData);

        $templateData = str_replace('$CAST$', implode(','.infy_nl_tab(1, 2), $this->generateCasts()), $templateData);

        $templateData = str_replace(
            '$RELATIONS$',
            fill_template($this->commandData->dynamicVars, implode(PHP_EOL.infy_nl_tab(1, 1), $this->generateRelations())),
            $templateData
        );

        $templateData = str_replace('$GENERATE_DATE$', date('F j, Y, g:i a T'), $templateData);

        return $templateData;
    }

    private function fillSoftDeletes($templateData)
    {
        if (!$this->commandData->getOption('softDelete')) {
            $templateData = str_replace('$SOFT_DELETE_IMPORT$', '', $templateData);
            $templateData = str_replace('$SOFT_DELETE$', '', $templateData);
            $templateData = str_replace('$SOFT_DELETE_DATES$', '', $templateData);
        } else {
            $templateData = str_replace(
                '$SOFT_DELETE_IMPORT$',
                'use Illuminate\Database\Eloquent\SoftDeletes;',
                $templateData
            );
            $templateData = str_replace('$SOFT_DELETE$', infy_tab()."use SoftDeletes;\n", $templateData);
            $deletedAtTimestamp = config('infyom.laravel_generator.timestamps.deleted_at', 'deleted_at');
            $templateData = str_replace(
                '$SOFT_DELETE_DATES$',
                infy_nl_tab()."protected \$dates = ['".$deletedAtTimestamp."'];\n",
                $templateData
            );
        }

        return $templateData;
    }

    private function fillHasFactory($templateData)
    {
        if (!$this->commandData->getAddOn('tests')) {
            $templateData = str_replace('$HAS_FACTORY_IMPORT$', '', $templateData);
            $templateData = str_replace('$HAS_FACTORY$', '', $templateData);
        } else {
            $templateData = str_replace(
                '$HAS_FACTORY_IMPORT$',
                'use Illuminate\Database\Eloquent\Factories\HasFactory;',
                $templateData
            );
            $templateData = str_replace('$HAS_FACTORY$', infy_tab()."use HasFactory;\n", $templateData);
        }

        return $templateData;
    }

    private function fillDocs($templateData)
    {
        if ($this->commandData->getAddOn('swagger')) {
            $templateData = $this->generateSwagger($templateData);
        }

        $docsTemplate = get_template('docs.model', 'laravel-generator');
        $docsTemplate = fill_template($this->commandData->dynamicVars, $docsTemplate);

        $fillables = '';
        $fieldsArr = [];
        $count = 1;
        foreach ($this->commandData->relations as $relation) {
            $field = $relationText = (isset($relation->inputs[0])) ? $relation->inputs[0] : null;
            if (in_array($field, $fieldsArr)) {
                $relationText = $relationText.'_'.$count;
                $count++;
            }

            $fillables .= ' * @property '.$this->getPHPDocType($relation->type, $relation, $relationText).PHP_EOL;
            $fieldsArr[] = $field;
        }

        foreach ($this->commandData->fields as $field) {
            if ($field->isFillable) {
                $fillables .= ' * @property '.$this->getPHPDocType($field->fieldType).' $'.$field->name.PHP_EOL;
            }
        }
        $docsTemplate = str_replace('$GENERATE_DATE$', date('F j, Y, g:i a T'), $docsTemplate);
        $docsTemplate = str_replace('$PHPDOC$', $fillables, $docsTemplate);

        $templateData = str_replace('$DOCS$', $docsTemplate, $templateData);

        return $templateData;
    }

    /**
     * @param $db_type
     * @param GeneratorFieldRelation|null $relation
     * @param string|null                 $relationText
     *
     * @return string
     */
    private function getPHPDocType($db_type, $relation = null, $relationText = null)
    {
        $relationText = (!empty($relationText)) ? $relationText : null;

        switch ($db_type) {
            case 'datetime':
                return 'string|\Carbon\Carbon';
            case '1t1':
                return '\\'.$this->commandData->config->nsModel.'\\'.$relation->inputs[0].' $'.Str::camel($relationText);
            case 'mt1':
                if (isset($relation->inputs[1])) {
                    $relationName = str_replace('_id', '', strtolower($relation->inputs[1]));
                } else {
                    $relationName = $relationText;
                }

                return '\\'.$this->commandData->config->nsModel.'\\'.$relation->inputs[0].' $'.Str::camel($relationName);
            case '1tm':
            case 'mtm':
            case 'hmt':
                return '\Illuminate\Database\Eloquent\Collection $'.Str::camel(Str::plural($relationText));
            default:
                $fieldData = SwaggerGenerator::getFieldType($db_type);
                if (!empty($fieldData['fieldType'])) {
                    return $fieldData['fieldType'];
                }

                return $db_type;
        }
    }

    public function generateSwagger($templateData)
    {
        $fieldTypes = SwaggerGenerator::generateTypes($this->commandData->fields);

        $template = get_template('model_docs.model', 'swagger-generator');

        $template = fill_template($this->commandData->dynamicVars, $template);

        $template = str_replace(
            '$REQUIRED_FIELDS$',
            '"'.implode('"'.', '.'"', $this->generateRequiredFields()).'"',
            $template
        );

        $propertyTemplate = get_template('model_docs.property', 'swagger-generator');

        $properties = SwaggerGenerator::preparePropertyFields($propertyTemplate, $fieldTypes);

        $template = str_replace('$PROPERTIES$', implode(",\n", $properties), $template);

        $templateData = str_replace('$DOCS$', $template, $templateData);

        return $templateData;
    }

    private function generateRequiredFields()
    {
        $requiredFields = [];

        foreach ($this->commandData->fields as $field) {
            if (!empty($field->validations)) {
                if (Str::contains($field->validations, 'required')) {
                    $requiredFields[] = $field->name;
                }
            }
        }

        return $requiredFields;
    }

    private function fillTimestamps($templateData)
    {
        $timestamps = TableFieldsGenerator::getTimestampFieldNames();

        $replace = '';
        if (empty($timestamps)) {
            $replace = infy_nl_tab()."public \$timestamps = false;\n";
        }

        if ($this->commandData->getOption('fromTable') && !empty($timestamps)) {
            list($created_at, $updated_at) = collect($timestamps)->map(function ($field) {
                return !empty($field) ? "'$field'" : 'null';
            });

            $replace .= infy_nl_tab()."const CREATED_AT = $created_at;";
            $replace .= infy_nl_tab()."const UPDATED_AT = $updated_at;\n";
        }

        return str_replace('$TIMESTAMPS$', $replace, $templateData);
    }

    private function generateRules()
    {
        $dont_require_fields = config('infyom.laravel_generator.options.hidden_fields', [])
                + config('infyom.laravel_generator.options.excluded_fields', $this->excluded_fields);

        $rules = [];

        foreach ($this->commandData->fields as $field) {
            if (!$field->isPrimary && !in_array($field->name, $dont_require_fields)) {
                if ($field->isNotNull && empty($field->validations)) {
                    $field->validations = 'required';
                }

                /**
                 * Generate some sane defaults based on the field type if we
                 * are generating from a database table.
                 */
                if ($this->commandData->getOption('fromTable')) {
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

    public function generateUniqueRules()
    {
        $tableNameSingular = Str::singular($this->commandData->config->tableName);
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

    public function generateCasts()
    {
        $casts = [];

        $timestamps = TableFieldsGenerator::getTimestampFieldNames();

        foreach ($this->commandData->fields as $field) {
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

    private function generateRelations()
    {
        $relations = [];

        $count = 1;
        $fieldsArr = [];
        foreach ($this->commandData->relations as $relation) {
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

        return $relations;
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->commandData->commandComment('Model file deleted: '.$this->fileName);
        }
    }
}

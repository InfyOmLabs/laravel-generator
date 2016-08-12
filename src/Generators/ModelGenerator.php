<?php

namespace InfyOm\Generator\Generators;

use DB;
use InfyOm\Generator\Common\CommandData;
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
    ];

    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path, $fileName, $table, $database;

    /** @var \Doctrine\DBAL\Schema\AbstractSchemaManager */
    private $schemaManager;

    /** @var array */
    private $tables,  $prep, $eloquentRules;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    /**
     * ModelGenerator constructor.
     *
     * @param \InfyOm\Generator\Common\CommandData $commandData
     */
    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathModel;
        $this->fileName = $this->commandData->modelName . '.php';
        $this->table = $this->commandData->dynamicVars['$TABLE_NAME$'];
//        if ($this->commandData->getOption('fromTable')) {
//            $this->prepareRelationships();
//        }
    }

//    public function prepareRelationships()
//    {
//        $this->getSchemaManager();
//        $this->getTables();
//        $this->getColumnsPrimaryAndForeignKeysPerTable();
//        $this->getEloquentRules();
//    }
//
//    private function getSchemaManager()
//    {
//        /*
//         * @todo allow from other that default DB conf
//         */
//        $this->connection = DB::connection()->getDoctrineConnection();
//        $this->connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
//        $this->connection->getDatabasePlatform()->registerDoctrineTypeMapping('bit', 'boolean');
//        $this->database = $this->connection->getDatabase();
//        $this->schemaManager = $this->connection->getSchemaManager();
//    }
//
//    private function getTables()
//    {
//        $this->tables = array_map(function (\Doctrine\DBAL\Schema\Table $x) {
//            return $x->getName();
//        }, $this->schemaManager->listTables());
//    }
//
//    private function getColumnsPrimaryAndForeignKeysPerTable()
//    {
//        $this->prep = [];
//        foreach ($this->tables as $table) {
//            $foreignKeys = $this->getForeignKeyConstraints($table);
//            $primaryKeys = $this->getPrimaryKeys($table);
//            $__columns = $this->schemaManager->listTableColumns($table);
//            $columns = [];
//            foreach ($__columns as $col) {
//                $columns[] = $col->toArray()['name'];
//            }
//            $this->prep[$table] = [
//                'foreign' => $foreignKeys,
//                'primary' => $primaryKeys,
//                'columns' => $columns,
//            ];
//        }
//    }
//
//    /**
//     * Get array of foreign keys.
//     *
//     * @param string $table Table Name
//     *
//     * @return array
//     */
//    public function getForeignKeyConstraints($table)
//    {
//        $fieldArr = [];
//        $foreignKeyConstraintArr = $this->schemaManager->listTableForeignKeys($table);
//
//        foreach ($foreignKeyConstraintArr as $foreignKeyConstraint) {
//            $fieldArr[] = [
//                'name' => $foreignKeyConstraint->getName(),
//                'field' => $foreignKeyConstraint->getLocalColumns()[0],
//                'references' => $foreignKeyConstraint->getForeignColumns()[0],
//                'on' => $foreignKeyConstraint->getForeignTableName(),
//                'onUpdate' => $foreignKeyConstraint->hasOption('onUpdate') ? $foreignKeyConstraint->getOption(
//                    'onUpdate'
//                ) : 'RESTRICT',
//                'onDelete' => $foreignKeyConstraint->hasOption('onDelete') ? $foreignKeyConstraint->getOption(
//                    'onDelete'
//                ) : 'RESTRICT',
//            ];
//        }
//
//        return $fieldArr;
//    }
//
//    /**
//     * Returns array of fields matched as primary keys in table.
//     * @param string $tableName
//     * @return array
//     */
//    private function getPrimaryKeys($tableName)
//    {
//        $primary_key_index = $this->schemaManager->listTableDetails($tableName)->getPrimaryKey();
//
//        return $primary_key_index ? $primary_key_index->getColumns() : [];
//    }
//
//    private function getEloquentRules()
//    {
//        $this->eloquentRules = [];
//        /*
//         * first create empty ruleset for each table
//         */
//        foreach ($this->prep as $table => $properties) {
//            $this->eloquentRules[$table] = [
//                'hasMany' => [],
//                'hasOne' => [],
//                'belongsTo' => [],
//                'belongsToMany' => [],
//                'fillable' => [],
//            ];
//        }
//
//        foreach ($this->prep as $table => $properties) {
//            $foreign = $properties['foreign'];
//            $primary = $properties['primary'];
//            $columns = $properties['columns'];
//
//            $this->setFillableProperties($table, $columns);
//
//            $isManyToMany = $this->detectManyToMany($table);
//
//            if ($isManyToMany === true) {
//                $this->addManyToManyRules($table);
//            }
//
//            /*
//             * the below used to be in an ELSE clause but we should be as verbose as possible
//             * when we detect a many-to-many table, we still want to set relations on it
//             */
//            foreach ($foreign as $fk) {
//                $isOneToOne = $this->detectOneToOne($fk, $primary);
//                if ($isOneToOne) {
//                    $this->addOneToOneRules($table, $fk);
//                } else {
//                    $this->addOneToManyRules($table, $fk);
//                }
//            }
//        }
//    }
//
//    /**
//     * @param $table
//     * @param $columns
//     * @param array $primary_keys
//     */
//    private function setFillableProperties($table, $columns, $primary_keys = ['id'])
//    {
//        $fillable = [];
//
//        $excluded = array_merge($this->excluded_fields, $primary_keys);
//
//        foreach ($columns as $column_name) {
//            if (!in_array($column_name, $excluded)) {
//                $fillable[] = "'$column_name'";
//            }
//        }
//        $this->eloquentRules[$table]['fillable'] = $fillable;
//    }
//
//    /**
//     * @param $table
//     *
//     * @return bool
//     *
//     * does this table have exactly two foreign keys that are also NOT primary,
//     * and no tables in the database refer to this table?
//     */
//    private function detectManyToMany($table)
//    {
//        $properties = $this->prep[$table];
//        $foreignKeys = $properties['foreign'];
//        $primaryKeys = $properties['primary'];
//
//        /*
//         * ensure we only have two foreign keys
//         */
//        if (count($foreignKeys) === 2) {
//
//            //ensure our foreign keys are not also defined as primary keys
//            $primaryKeyCountThatAreAlsoForeignKeys = 0;
//            foreach ($foreignKeys as $foreign) {
//                foreach ($primaryKeys as $primary) {
//                    if ($primary === $foreign['name']) {
//                        ++$primaryKeyCountThatAreAlsoForeignKeys;
//                    }
//                }
//            }
//
//            if ($primaryKeyCountThatAreAlsoForeignKeys === 1) {
//                /*
//                 * one of the keys foreign keys was also a primary key this
//                 * is not a many to many. (many to many is only possible when
//                 * both or none of the foreign keys are also primary)
//                 */
//                return false;
//            }
//
//            /*
//             * ensure no other tables refer to this one
//             */
//            foreach ($this->prep as $compareTable => $properties) {
//                if ($table !== $compareTable) {
//                    foreach ($properties['foreign'] as $prop) {
//                        if ($prop['on'] === $table) {
//                            return false;
//                        }
//                    }
//                }
//            }
//
//            /*
//             * this is a many to many table!
//             */
//
//            return true;
//        }
//
//        return false;
//    }
//
//    /**
//     * @param $table string
//     */
//    private function addManyToManyRules($table)
//    {
//        $foreign = $this->prep[$table]['foreign'];
//
//        $fk1 = $foreign[0];
//        $fk1Table = $fk1['on'];
//        $fk1Field = $fk1['field'];
//
//        $fk2 = $foreign[1];
//        $fk2Table = $fk2['on'];
//        $fk2Field = $fk2['field'];
//
//        if (in_array($fk1Table, $this->tables)) {
//            $this->eloquentRules[$fk1Table]['belongsToMany'][] = [$fk2Table, $table, $fk1Field, $fk2Field];
//        }
//        if (in_array($fk2Table, $this->tables)) {
//            $this->eloquentRules[$fk2Table]['belongsToMany'][] = [$fk1Table, $table, $fk2Field, $fk1Field];
//        }
//    }
//
//    /**
//     * @param $fk
//     * @param $primary
//     *
//     * @return bool
//     *
//     * if FK is also a primary key, and there is only one primary key, we know
//     * this will be a one to one relationship
//     */
//    private function detectOneToOne($fk, $primary)
//    {
//        if (count($primary) === 1) {
//            foreach ($primary as $prim) {
//                if ($prim === $fk['field']) {
//                    return true;
//                }
//            }
//        }
//
//        return false;
//    }
//
//    /**
//     * @param $table
//     * @param $fk
//     */
//    private function addOneToOneRules($table, $fk)
//    {
//        $fkTable = $fk['on'];
//        $field = $fk['field'];
//        $references = $fk['references'];
//        if (in_array($fkTable, $this->tables)) {
//            $this->eloquentRules[$fkTable]['hasOne'][] = [$table, $field, $references];
//        }
//        if (in_array($table, $this->tables)) {
//            $this->eloquentRules[$table]['belongsTo'][] = [$fkTable, $field, $references];
//        }
//    }
//
//    /**
//     * @param $table string
//     * @param $fk array
//     */
//    private function addOneToManyRules($table, $fk)
//    {
//        $fkTable = $fk['on'];
//        $field = $fk['field'];
//        $references = $fk['references'];
//        if (in_array($fkTable, $this->tables)) {
//            $this->eloquentRules[$fkTable]['hasMany'][] = [$table, $field, $references];
//        }
//        if (in_array($table, $this->tables)) {
//            $this->eloquentRules[$table]['belongsTo'][] = [$fkTable, $field, $references];
//        }
//    }

    public function generate()
    {
        $templateData = get_template('models.model', 'laravel-generator');

        $templateData = $this->fillTemplate($templateData);

        FileUtil::createFile($this->path, $this->fileName, $templateData);

        $this->commandData->commandComment("\nModel created: ");
        $this->commandData->commandInfo($this->fileName);
    }

    private function fillTemplate($templateData)
    {
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $templateData = $this->fillSoftDeletes($templateData);

        $fillables = [];

        foreach ($this->commandData->inputFields as $field) {
            if ($field->isFillable) {
                $fillables[] = "'" . $field->name . "'";
            }
        }

        $templateData = $this->fillDocs($templateData);

        $templateData = $this->fillTimestamps($templateData);

        if ($this->commandData->getOption('primary')) {
            $primary = infy_tab() . "protected \$primaryKey = '" . $this->commandData->getOption('primary') . "';\n";
        } else {
            $primary = '';
        }

        $templateData = str_replace('$PRIMARY$', $primary, $templateData);

        $templateData = str_replace('$FIELDS$', implode(',' . infy_nl_tab(1, 2), $fillables), $templateData);

        $templateData = str_replace('$RULES$', implode(',' . infy_nl_tab(1, 2), $this->generateRules()), $templateData);

        $templateData = str_replace('$CAST$', implode(',' . infy_nl_tab(1, 2), $this->generateCasts()), $templateData);

//        $templateData = str_replace(
//            '$ELOQUENTFUNCTIONS$', implode(PHP_EOL . infy_nl_tab(1, 1), $this->generateEloquent()), $templateData
//        );

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
                '$SOFT_DELETE_IMPORT$', "use Illuminate\\Database\\Eloquent\\SoftDeletes;\n",
                $templateData
            );
            $templateData = str_replace('$SOFT_DELETE$', infy_tab() . "use SoftDeletes;\n", $templateData);
            $deletedAtTimestamp = config('infyom.laravel_generator.timestamps.deleted_at', 'deleted_at');
            $templateData = str_replace(
                '$SOFT_DELETE_DATES$', infy_nl_tab() . "protected \$dates = ['" . $deletedAtTimestamp . "'];\n",
                $templateData
            );
        }

        return $templateData;
    }

    private function fillDocs($templateData)
    {
        if ($this->commandData->getAddOn('swagger')) {
            $templateData = $this->generateSwagger($templateData);
        } else {
            $docsTemplate = get_template('docs.model', 'laravel-generator');
            $docsTemplate = fill_template($this->commandData->dynamicVars, $docsTemplate);
            $docsTemplate = str_replace('$GENERATE_DATE$', date('F j, Y, g:i a T'), $docsTemplate);

            $templateData = str_replace('$DOCS$', $docsTemplate, $templateData);
        }

        return $templateData;
    }

    public function generateSwagger($templateData)
    {
        $fieldTypes = SwaggerGenerator::generateTypes($this->commandData->inputFields);

        $template = get_template('model.model', 'swagger-generator');

        $template = fill_template($this->commandData->dynamicVars, $template);

        $template = str_replace('$REQUIRED_FIELDS$',
            '"' . implode('"' . ', ' . '"', $this->generateRequiredFields()) . '"', $template);

        $propertyTemplate = get_template('model.property', 'swagger-generator');

        $properties = SwaggerGenerator::preparePropertyFields($propertyTemplate, $fieldTypes);

        $template = str_replace('$PROPERTIES$', implode(",\n", $properties), $template);

        $templateData = str_replace('$DOCS$', $template, $templateData);

        return $templateData;
    }

    private function generateRequiredFields()
    {
        $requiredFields = [];

        foreach ($this->commandData->inputFields as $field) {
            if (!empty($field->validations)) {
                if (str_contains($field->validations, 'required')) {
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

        if ($this->commandData->getOption('fromTable')) {
            if (empty($timestamps)) {
                $replace = infy_nl_tab() . "public \$timestamps = false;\n";
            } else {
                list($created_at, $updated_at) = collect($timestamps)->map(function ($field) {
                    return !empty($field) ? "'$field'" : 'null';
                });

                $replace .= infy_nl_tab() . "const CREATED_AT = $created_at;";
                $replace .= infy_nl_tab() . "const UPDATED_AT = $updated_at;\n";
            }
        }

        return str_replace('$TIMESTAMPS$', $replace, $templateData);
    }

    private function generateRules()
    {
        $rules = [];

        foreach ($this->commandData->inputFields as $field) {
            if (!empty($field->validations)) {
                $rule = "'" . $field->name . "' => '" . $field->validations . "'";
                $rules[] = $rule;
            }
        }

        return $rules;
    }

    public function generateCasts()
    {
        $casts = [];

        $timestamps = TableFieldsGenerator::getTimestampFieldNames();

        foreach ($this->commandData->inputFields as $field) {
            if (in_array($field->name, $timestamps)) {
                continue;
            }

            $rule = "'" . $field->name . "' => ";

            switch ($field->fieldType) {
                case 'integer':
                    $rule .= "'integer'";
                    break;
                case 'double':
                    $rule .= "'double'";
                    break;
                case 'float':
                    $rule .= "'float'";
                    break;
                case 'boolean':
                    $rule .= "'boolean'";
                    break;
                case 'dateTime':
                case 'dateTimeTz':
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

//    /**
//     * @return mixed
//     */
//    private function generateEloquent()
//    {
//        if (isset($this->eloquentRules[$this->table])) {
//            return array_merge(
//                $this->generateBelongsToFunctions($this->eloquentRules[$this->table]['belongsTo']),
//                $this->generateBelongsToManyFunctions($this->eloquentRules[$this->table]['belongsToMany']),
//                $this->generateHasManyFunctions($this->eloquentRules[$this->table]['hasMany']),
//                $this->generateHasOneFunctions($this->eloquentRules[$this->table]['hasOne'])
//            );
//        }
//
//        return [];
//    }
//
//    /**
//     * @param array $rulesContainerArr
//     *
//     * @return array
//     */
//    private function generateBelongsToFunctions($rulesContainerArr = [])
//    {
//        $functionArr = [];
//        foreach ($rulesContainerArr as $rulesContainerRule) {
//            $belongsToModel = $this->generateModelNameFromTableName($rulesContainerRule[0]);
//            $belongsToFunctionName = $this->getSingularFunctionName($belongsToModel);
//            $templateData = get_template('models.belongsTo', 'laravel-generator');
//
//            $templateData = str_replace('$FUNCTION_NAME$', $belongsToFunctionName, $templateData);
//            $templateData = str_replace(
//                '$NAMESPACE_MODEL$', $this->commandData->dynamicVars['$NAMESPACE_MODEL$'], $templateData
//            );
//            $templateData = str_replace('$RELATION_MODEL_NAME$', $belongsToModel, $templateData);
//            $templateData = str_replace('$KEY1$', $rulesContainerRule[1], $templateData);
//            $templateData = str_replace('$KEY2$', $rulesContainerRule[2], $templateData);
//            $templateData = str_replace('$GENERATE_DATE$', date('F j, Y, g:i a T'), $templateData);
//
//            $functionArr[] = $templateData;
//        }
//
//        return $functionArr;
//    }
//
//    /**
//     * @param string $table
//     *
//     * @return string
//     */
//    private function generateModelNameFromTableName($table)
//    {
//        return ucfirst(camel_case(str_singular($table)));
//    }
//
//    /**
//     * @param string $modelName
//     *
//     * @return string
//     */
//    private function getSingularFunctionName($modelName)
//    {
//        $modelName = lcfirst($modelName);
//
//        return str_singular($modelName);
//    }
//
//    /**
//     * @param array $rulesContainerArr
//     *
//     * @return array
//     */
//    private function generateBelongsToManyFunctions($rulesContainerArr = [])
//    {
//        $functionArr = [];
//        foreach ($rulesContainerArr as $rulesContainerRule) {
//            $belongsToManyModel = $this->generateModelNameFromTableName($rulesContainerRule[0]);
//            $belongsToManyFunctionName = $this->getPluralFunctionName($belongsToManyModel);
//            $templateData = get_template('models.belongsToMany', 'laravel-generator');
//
//            $templateData = str_replace('$FUNCTION_NAME$', $belongsToManyFunctionName, $templateData);
//            $templateData = str_replace(
//                '$NAMESPACE_MODEL$', $this->commandData->dynamicVars['$NAMESPACE_MODEL$'], $templateData
//            );
//            $templateData = str_replace('$RELATION_MODEL_NAME$', $belongsToManyModel, $templateData);
//            $templateData = str_replace('$THROUGH$', $rulesContainerRule[1], $templateData);
//            $templateData = str_replace('$KEY1$', $rulesContainerRule[2], $templateData);
//            $templateData = str_replace('$KEY2$', $rulesContainerRule[3], $templateData);
//            $templateData = str_replace('$GENERATE_DATE$', date('F j, Y, g:i a T'), $templateData);
//
//            $functionArr[] = $templateData;
//        }
//
//        return $functionArr;
//    }
//
//    /**
//     * @param string $modelName
//     *
//     * @return string
//     */
//    private function getPluralFunctionName($modelName)
//    {
//        $modelName = lcfirst($modelName);
//
//        return str_plural($modelName);
//    }
//
//    /**
//     * @param array $rulesContainerArr
//     *
//     * @return array
//     */
//    private function generateHasManyFunctions($rulesContainerArr = [])
//    {
//        $functionArr = [];
//        foreach ($rulesContainerArr as $rulesContainerRule) {
//            $hasManyModel = $this->generateModelNameFromTableName($rulesContainerRule[0]);
//            $hasManyFunctionName = $this->getPluralFunctionName($hasManyModel);
//            $templateData = get_template('models.hasMany', 'laravel-generator');
//
//            $templateData = str_replace('$FUNCTION_NAME$', $hasManyFunctionName, $templateData);
//            $templateData = str_replace(
//                '$NAMESPACE_MODEL$', $this->commandData->dynamicVars['$NAMESPACE_MODEL$'], $templateData
//            );
//            $templateData = str_replace('$RELATION_MODEL_NAME$', $hasManyModel, $templateData);
//            $templateData = str_replace('$KEY1$', $rulesContainerRule[1], $templateData);
//            $templateData = str_replace('$KEY2$', $rulesContainerRule[2], $templateData);
//            $templateData = str_replace('$GENERATE_DATE$', date('F j, Y, g:i a T'), $templateData);
//
//            $functionArr[] = $templateData;
//        }
//
//        return $functionArr;
//    }
//
//    /**
//     * @param array $rulesContainerArr
//     *
//     * @return array
//     */
//    private function generateHasOneFunctions($rulesContainerArr = [])
//    {
//        $functionArr = [];
//        foreach ($rulesContainerArr as $rulesContainerRule) {
//            $hasOneModel = $this->generateModelNameFromTableName($rulesContainerRule[0]);
//            $hasOneFunctionName = $this->getSingularFunctionName($hasOneModel);
//            $templateData = get_template('models.hasOne', 'laravel-generator');
//
//            $templateData = str_replace('$FUNCTION_NAME$', $hasOneFunctionName, $templateData);
//            $templateData = str_replace(
//                '$NAMESPACE_MODEL$', $this->commandData->dynamicVars['$NAMESPACE_MODEL$'], $templateData
//            );
//            $templateData = str_replace('$RELATION_MODEL_NAME$', $hasOneModel, $templateData);
//            $templateData = str_replace('$KEY1$', $rulesContainerRule[1], $templateData);
//            $templateData = str_replace('$KEY2$', $rulesContainerRule[2], $templateData);
//            $templateData = str_replace('$GENERATE_DATE$', date('F j, Y, g:i a T'), $templateData);
//
//            $functionArr[] = $templateData;
//        }
//
//        return $functionArr;
//    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->commandData->commandComment('Model file deleted: ' . $this->fileName);
        }
    }
}

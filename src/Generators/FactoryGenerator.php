<?php

namespace InfyOm\Generator\Generators;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Utils\FileUtil;
use InfyOm\Generator\Utils\GeneratorFieldsInputUtil;
use Illuminate\Support\Str;

/**
 * Class FactoryGenerator.
 */
class FactoryGenerator extends BaseGenerator
{
    /** @var CommandData */
    private $commandData;
    /** @var string */
    private $path;
    /** @var string */
    private $fileName;

    private $relations = [];

    /**
     * FactoryGenerator constructor.
     *
     * @param CommandData $commandData
     */
    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathFactory;
        $this->fileName = $this->commandData->modelName.'Factory.php';
        //setup relations if available
        //assumes relation fields are tailed with _id if not supplied
        if(property_exists($this->commandData, 'relations')) {
            foreach ($this->commandData->relations as $relation) {
                if ($relation->type == 'mt1') {
                    $relation = (isset($relation->inputs[0])) ? $relation->inputs[0] : null;
                    $field = false;
                    if (isset($relations->inputs[1])) {
                        $field = $relation->inputs[1];
                    } else {
                        $field = Str::snake($relation)."_id";
                    }                    
                    if ($field) {
                        $rel = $relation;
                        $this->relations[$field] = [
                            'relation' => $rel,
                            'model_class' => $this->commandData->config->nsModel."\\".$relation
                        ];
                    }
                }
            }
        }
    }

    public function generate()
    {
        $templateData = get_template('factories.model_factory', 'laravel-generator');

        $templateData = $this->fillTemplate($templateData);

        FileUtil::createFile($this->path, $this->fileName, $templateData);

        $this->commandData->commandObj->comment("\nFactory created: ");
        $this->commandData->commandObj->info($this->fileName);
    }

    /**
     * @param string $templateData
     *
     * @return mixed|string
     */
    private function fillTemplate($templateData)
    {
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $templateData = str_replace(
            '$FIELDS$',
            implode(','.infy_nl_tab(1, 3), $this->generateFields()),
            $templateData
        );

        $extra = $this->getRelationsBootstrap();

        $templateData = str_replace(
            '$RELATION_USES$',
            $extra['uses'],
            $templateData
        );

        $templateData = str_replace(
            '$RELATIONS$',
            $extra['text'],
            $templateData
        );

        return $templateData;
    }

    /**
     * @return array
     */
    private function generateFields()
    {
        $fields = [];
        
        //get model validation rules
        $class = $this->commandData->config->nsModel."\\".$this->commandData->modelName;
        $rules = $class::$rules;
        $relations = array_keys($this->relations);

        foreach ($this->commandData->fields as $field) {
            if ($field->isPrimary) {
                continue;
            }

            $fieldData = "'".$field->name."' => ".'$this->faker->';
            $rule = null;
            if (isset($rules[$field->name])) {
                $rule = $rules[$field->name];
            }

            switch ($field->fieldType) {
                case 'integer':
                case 'smallinteger':
                    $fakerData = in_array($field->name, $relations) ? ":relation" : $this->getValidNumber($rule);
                    break;
                case 'long':
                case 'biginteger':
                case 'float':
                case 'double':
                case 'decimal':                    
                    $fakerData = $this->getValidNumber($rule);
                    break;
                case 'char':
                    $fakerData = 'text(1)';
                    break;
                case 'string':
                    if (!$rule) {
                        $rule = 'max:255';
                    }
                    $fakerData = $this->getValidText($rule);
                    break;
                case 'text':
                    $fakerData = $rule ? $this->getValidText($rule) : 'text(500)';
                case 'boolean':
                    $fakerData = "boolean";
                    break;
                case 'date':
                    $fakerData = "date('Y-m-d')";
                    break;
                case 'datetime':
                case 'timestamp':
                    $fakerData = "date('Y-m-d H:i:s')";
                    break;
                case 'time':
                    $fakerData = "date('H:i:s')";
                    break;
                case 'enum':
                    $fakerData = 'randomElement('.
                        GeneratorFieldsInputUtil::prepareValuesArrayStr($field->htmlValues).
                        ')';
                    break;
                default:
                    $fakerData = 'word';
            }

            if ($fakerData == ":relation") {
                $fieldData = $this->getValidRelation($field->name);
            } else {
                $fieldData .= $fakerData;
            }

            $fields[] = $fieldData;
        }

        return $fields;
    }

    /**
     * Generates a valid number based on applicable model rule
     * @param string $rule The applicable model rule
     * @return string
     */
    public function getValidNumber($rule = null)
    {
        if ($rule) {
            $max = $this->extractMinMax($rule, 'max') ?? PHP_INT_MAX;
            $min = $this->extractMinMax($rule, 'min') ?? 0;
            return "numberBetween($min, $max)";
        }
        else {
            return "randomDigitNotNull";
        }
    }

    /**
     * Generates a valid relation if applicable
     * This method assumes the related field primary key is id
     * @param string $fieldName The field name
     * @return string
     */
    public function getValidRelation($fieldName)
    {
        $relation = $this->relations[$fieldName]['relation'];
        $variable = Str::camel($relation);
        return "'".$fieldName."' => ".'$'.$variable."->id";
    }

    /**
     * Generates a valid text based on applicable model rule
     * @param string $rule The applicable model rule
     * @return string
     */
    public function getValidText($rule = null)
    {
        if ($rule) {
            $max = $this->extractMinMax($rule, 'max') ?? 4096;
            $min = $this->extractMinMax($rule, 'min') ?? 0;
            return "text($this->faker->numberBetween($min, $max))";
        }
        else {
            return "text";
        }
    }

    /**
     * Extracts min or max rule for a laravel model
     */
    public function extractMinMax($rule, $t = 'min'){
        $i = strpos($rule, $t);
        $e = strpos($rule, "|",  $i);
        if ($e === FALSE) {
            $e = strlen($rule);
        }
        if ($i !== FALSE) {
            $len = $e - ($i+4);
            $result = substr($rule, $i+4, $len);
            return $result;
        }
        return null;
    }
    
    /**
     * Generate valid model so we can use the id where applicable
     * This method assumes the model has a factory
     */
    public function getRelationsBootstrap()
    {
        $text = "";
        $uses = "";
        foreach ($this->relations as $field => $data) {
            $relation = $data['relation'];
            $qualifier = $data['model_class'];
            $variable = Str::camel($relation);
            $model = Str::studly($relation);
            $text .= infy_nl_tab(1, 2).'$'.$relation. '= '.$variable."::first();".
            infy_nl_tab(1, 2).
            'if (!$'.$relation.') {'.
            infy_nl_tab(1, 3).
            '$'.$relation.' = '.$model.'::factory()->create();'.
            infy_nl_tab(1, 2).'}';
            $uses .= infy_nl()."Use $qualifier;";
        }
        return [
            'text' => $text,
            'uses' => $uses
        ];
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->commandData->commandComment('Factory file deleted: '.$this->fileName);
        }
    }
}

<?php

namespace InfyOm\Generator\Common;

class GeneratorFieldRelation
{
    /** @var string */
    public $type;
    public $inputs;

    public static function parseRelation($relationInput)
    {
        $inputs = explode(',', $relationInput);

        $relation = new self();
        $relation->type = array_shift($inputs);
        $relation->inputs = $inputs;

        return $relation;
    }

    public function getRelationFunctionText()
    {
        $modelName = $this->inputs[0];
        switch ($this->type) {
            case '1t1':
                $functionName = camel_case($modelName);
                $relation = 'hasOne';
                $relationClass = 'HasOne';
                break;
            case '1tm':
                $functionName = camel_case(str_plural($modelName));
                $relation = 'hasMany';
                $relationClass = 'HasMany';
                break;
            case 'mt1':
                $functionName = camel_case($modelName);
                $relation = 'belongsTo';
                $relationClass = 'BelongsTo';
                break;
            case 'mtm':
                $functionName = camel_case(str_plural($modelName));
                $relation = 'belongsToMany';
                $relationClass = 'BelongsToMany';
                break;
            case 'hmt':
                $functionName = camel_case(str_plural($modelName));
                $relation = 'hasManyThrough';
                $relationClass = 'HasManyThrough';
                break;
            default:
                $functionName = '';
                $relation = '';
                $relationClass = '';
                break;
        }

        if (!empty($functionName) and !empty($relation)) {
            return $this->generateRelation($functionName, $relation, $relationClass);
        }

        return '';
    }

    private function generateRelation($functionName, $relation, $relationClass)
    {
        $modelName = array_shift($this->inputs);

        $template = get_template('model.relationship', 'laravel-generator');

        $template = str_replace('$RELATIONSHIP_CLASS$', $relationClass, $template);
        $template = str_replace('$FUNCTION_NAME$', $functionName, $template);
        $template = str_replace('$RELATION$', $relation, $template);
        $template = str_replace('$RELATION_MODEL_NAME$', $modelName, $template);

        if (count($this->inputs) > 0) {
            $inputFields = implode("', '", $this->inputs);
            $inputFields = ", '".$inputFields."'";
        } else {
            $inputFields = '';
        }

        $template = str_replace('$INPUT_FIELDS$', $inputFields, $template);

        return $template;
    }
}

<?php

namespace InfyOm\Generator\Common;

use Illuminate\Support\Str;

class GeneratorFieldRelation
{
    public $type;
    public array $inputs;
    public string $relationName;

    public static function parseRelation($relationInput): self
    {
        $inputs = explode(',', $relationInput);

        $relation = new self();
        $relation->type = array_shift($inputs);
        $modelWithRelation = explode(':', array_shift($inputs)); //e.g ModelName:relationName
        if (count($modelWithRelation) == 2) {
            $relation->relationName = $modelWithRelation[1];
            unset($modelWithRelation[1]);
        }
        $relation->inputs = array_merge($modelWithRelation, $inputs);

        return $relation;
    }

    public function getRelationFunctionText(string $relationText = null): string
    {
        $singularRelation = (!empty($this->relationName)) ? $this->relationName : Str::camel($relationText);
        $pluralRelation = (!empty($this->relationName)) ? $this->relationName : Str::camel(Str::plural($relationText));

        switch ($this->type) {
            case '1t1':
                $functionName = $singularRelation;
                $relation = 'hasOne';
                $relationClass = 'HasOne';
                break;
            case '1tm':
                $functionName = $pluralRelation;
                $relation = 'hasMany';
                $relationClass = 'HasMany';
                break;
            case 'mt1':
                if (!empty($this->relationName)) {
                    $singularRelation = $this->relationName;
                } elseif (isset($this->inputs[1])) {
                    $singularRelation = Str::camel(str_replace('_id', '', strtolower($this->inputs[1])));
                }
                $functionName = $singularRelation;
                $relation = 'belongsTo';
                $relationClass = 'BelongsTo';
                break;
            case 'mtm':
                $functionName = $pluralRelation;
                $relation = 'belongsToMany';
                $relationClass = 'BelongsToMany';
                break;
            case 'hmt':
                $functionName = $pluralRelation;
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

    protected function generateRelation($functionName, $relation, $relationClass)
    {
        $inputs = $this->inputs;
        $relatedModelName = array_shift($inputs);

        if (count($inputs) > 0) {
            $inputFields = implode("', '", $inputs);
            $inputFields = ", '".$inputFields."'";
        } else {
            $inputFields = '';
        }

        return view('laravel-generator::model.relationship', [
            'relationClass' => $relationClass,
            'functionName'  => $functionName,
            'relation'      => $relation,
            'relatedModel'  => $relatedModelName,
            'fields'        => $inputFields,
        ])->render();
    }
}

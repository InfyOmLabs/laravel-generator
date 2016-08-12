<?php

namespace InfyOm\Generator\Common;

use Illuminate\Support\Str;

class GeneratorField
{
    /** @var  string */
    public $name, $dbInput, $htmlType, $fieldType;

    /** @var  array */
    public $htmlValues;

    /** @var  string */
    public $migrationText, $foreignKeyText, $validations;

    /** @var  boolean */
    public $isSearchable = true, $isFillable = true, $isPrimary = false, $inForm = true, $inIndex = true;

    /** @var  GeneratorFieldRelation[] */
    public $relations;

    public function parseDBInput($dbInput)
    {
        $this->dbInput = $dbInput;
        $this->prepareMigrationText();
    }

    public function parseHtmlInput($htmlInput)
    {
        $this->htmlValues = [];

        if(empty($htmlInput)) {
            $this->htmlType = 'text';
            return;
        }

        $inputsArr = explode(",", $htmlInput);

        $this->htmlType = array_shift($inputsArr);

        if(count($inputsArr) > 0) {
            $this->htmlValues = $inputsArr;
        }
    }

    public function parseOptions($options)
    {
        $options = strtolower($options);
        $optionsArr = explode(",", $options);
        if (in_array("s", $optionsArr)) {
            $this->isSearchable = false;
        }
        if (in_array("p", $optionsArr)) {
            $this->isPrimary = true;
        }
        if (in_array("f", $optionsArr)) {
            $this->isFillable = false;
        }
        if (in_array("if", $optionsArr)) {
            $this->inForm = false;
        }
        if (in_array("ii", $optionsArr)) {
            $this->inIndex = false;
        }
    }

    public function parseRelation($relationInput)
    {
        $inputs = explode(",", $relationInput);

        $relation = new GeneratorFieldRelation();
        $relation->type = array_shift($inputs);
        $relation->inputs = $inputs;
    }

    private function prepareMigrationText()
    {
        $inputsArr = explode(":", $this->dbInput);
        $this->migrationText = '$table->';

        $fieldTypeParams = explode(",", array_shift($inputsArr));
        $this->fieldType = array_shift($fieldTypeParams);
        $this->migrationText .= $this->fieldType . "('" . $this->name . "'";

        foreach ($fieldTypeParams as $param) {
            $this->migrationText .= ", " . $param;
        }

        $this->migrationText .= ")";

        foreach ($inputsArr as $input) {
            $inputParams = explode(",", $input);
            $functionName = array_shift($inputParams);
            if ($functionName == "foreign") {
                $foreignTable = array_shift($inputParams);
                $foreignField = array_shift($inputParams);
                $this->foreignKeyText .= "\$table->foreign('" . $this->name . "')->references('" . $foreignField . "')->on('" . $foreignTable . "');";
            } else {
                $this->migrationText .= "->" . $functionName;
                $this->migrationText .= "(";
                foreach ($inputParams as $param) {
                    $this->migrationText .= ", " . $param;
                }
                $this->migrationText .= ")";
            }
        }

        $this->migrationText .= ";";
    }

    public static function parseFieldFromFile($fieldInput)
    {
        $field = new GeneratorField();
        $field->name = $fieldInput['name'];
        $field->parseDBInput($fieldInput['dbInput']);
        $field->parseHtmlInput(isset($fieldInput['htmlType']) ? $fieldInput['htmlType'] : '');
        $field->validations = isset($fieldInput['validations']) ? $fieldInput['validations'] : '';
        $field->isSearchable = isset($fieldInput['searchable']) ? $fieldInput['searchable'] : false;
        $field->isFillable = isset($fieldInput['fillable']) ? $fieldInput['fillable'] : true;
        $field->isPrimary = isset($fieldInput['primary']) ? $fieldInput['primary'] : false;
        $field->inForm = isset($fieldInput['inForm']) ? $fieldInput['inForm'] : true;
        $field->inIndex = isset($fieldInput['inIndex']) ? $fieldInput['inIndex'] : true;
        return $field;
    }

    public function __get($key)
    {
        if ($key == 'fieldTitle') {
            return Str::title(str_replace('_', ' ', $this->name));
        }

        return $this->$key;
    }
}
<?php

namespace InfyOm\Generator\Common;

use Illuminate\Support\Str;

class GeneratorField
{
    /** @var string */
    public $name;
    public $dbInput;
    public $htmlInput;
    public $htmlType;
    public $fieldType;
    public $description;

    /** @var array */
    public $htmlValues;

    /** @var string */
    public $migrationText;
    public $foreignKeyText;
    public $validations;

    /** @var bool */
    public $isSearchable = true;
    public $isFillable = true;
    public $isPrimary = false;
    public $inForm = true;
    public $inIndex = true;
    public $inView = true;
    public $isNotNull = false;

    /** @var int */
    public $numberDecimalPoints = 2;

    /**
     * @param Column $column
     * @param $dbInput
     */
    public function parseDBType($dbInput, $column = null)
    {
        $this->dbInput = $dbInput;
        if (!is_null($column)) {
            $this->dbInput = ($column->getLength() > 0) ? $this->dbInput.','.$column->getLength() : $this->dbInput;
            $this->dbInput = (!$column->getNotnull()) ? $this->dbInput.':nullable' : $this->dbInput;
        }
        $this->prepareMigrationText();
    }

    public function parseHtmlInput($htmlInput)
    {
        $this->htmlInput = $htmlInput;
        $this->htmlValues = [];

        if (empty($htmlInput)) {
            $this->htmlType = 'text';

            return;
        }

        if (Str::contains($htmlInput, 'selectTable')) {
            $inputsArr = explode(':', $htmlInput);
            $this->htmlType = array_shift($inputsArr);
            $this->htmlValues = $inputsArr;

            return;
        }

        $inputsArr = explode(',', $htmlInput);

        $this->htmlType = array_shift($inputsArr);

        if (count($inputsArr) > 0) {
            $this->htmlValues = $inputsArr;
        }
    }

    public function parseOptions($options)
    {
        $options = strtolower($options);
        $optionsArr = explode(',', $options);
        if (in_array('s', $optionsArr)) {
            $this->isSearchable = false;
        }
        if (in_array('p', $optionsArr)) {
            // if field is primary key, then its not searchable, fillable, not in index & form
            $this->isPrimary = true;
            $this->isSearchable = false;
            $this->isFillable = false;
            $this->inForm = false;
            $this->inIndex = false;
            $this->inView = false;
        }
        if (in_array('f', $optionsArr)) {
            $this->isFillable = false;
        }
        if (in_array('if', $optionsArr)) {
            $this->inForm = false;
        }
        if (in_array('ii', $optionsArr)) {
            $this->inIndex = false;
        }
        if (in_array('iv', $optionsArr)) {
            $this->inView = false;
        }
    }

    private function prepareMigrationText()
    {
        $inputsArr = explode(':', $this->dbInput);
        $this->migrationText = '$table->';

        $fieldTypeParams = explode(',', array_shift($inputsArr));
        $this->fieldType = array_shift($fieldTypeParams);
        $this->migrationText .= $this->fieldType."('".$this->name."'";

        if ($this->fieldType == 'enum') {
            $this->migrationText .= ', [';
            foreach ($fieldTypeParams as $param) {
                $this->migrationText .= "'".$param."',";
            }
            $this->migrationText = substr($this->migrationText, 0, strlen($this->migrationText) - 1);
            $this->migrationText .= ']';
        } else {
            foreach ($fieldTypeParams as $param) {
                $this->migrationText .= ', '.$param;
            }
        }

        $this->migrationText .= ')';

        foreach ($inputsArr as $input) {
            $inputParams = explode(',', $input);
            $functionName = array_shift($inputParams);
            if ($functionName == 'foreign') {
                $foreignTable = array_shift($inputParams);
                $foreignField = array_shift($inputParams);
                $this->foreignKeyText .= "\$table->foreign('".$this->name."')->references('".$foreignField."')->on('".$foreignTable."')";
                if (count($inputParams)) {
                    $cascade = array_shift($inputParams);
                    if ($cascade == 'cascade') {
                        $this->foreignKeyText .= "->onUpdate('cascade')->onDelete('cascade')";
                    }
                }
                $this->foreignKeyText .= ';';
            } else {
                $this->migrationText .= '->'.$functionName;
                $this->migrationText .= '(';
                $this->migrationText .= implode(', ', $inputParams);
                $this->migrationText .= ')';
            }
        }

        $this->migrationText .= ';';
    }

    public static function parseFieldFromFile($fieldInput)
    {
        $field = new self();
        $field->name = $fieldInput['name'];
        $field->parseDBType($fieldInput['dbType']);
        $field->parseHtmlInput(isset($fieldInput['htmlType']) ? $fieldInput['htmlType'] : '');
        $field->validations = isset($fieldInput['validations']) ? $fieldInput['validations'] : '';
        $field->isSearchable = isset($fieldInput['searchable']) ? $fieldInput['searchable'] : false;
        $field->isFillable = isset($fieldInput['fillable']) ? $fieldInput['fillable'] : true;
        $field->isPrimary = isset($fieldInput['primary']) ? $fieldInput['primary'] : false;
        $field->inForm = isset($fieldInput['inForm']) ? $fieldInput['inForm'] : true;
        $field->inIndex = isset($fieldInput['inIndex']) ? $fieldInput['inIndex'] : true;
        $field->inView = isset($fieldInput['inView']) ? $fieldInput['inView'] : true;

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

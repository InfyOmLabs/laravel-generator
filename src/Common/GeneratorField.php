<?php

namespace InfyOm\Generator\Common;

use Illuminate\Support\Str;

class GeneratorField
{
    /** @var string */
    public string $name;
    public string $dbType;
    public array $dbTypeParams = [];
    public array $dbExtraFunctions = [];

    public string $htmlType = '';
    public array $htmlValues = [];

    public string $description;
    public string $validations = '';
    public bool $isSearchable = true;
    public bool $isFillable = true;
    public bool $isPrimary = false;
    public bool $inForm = true;
    public bool $inIndex = true;
    public bool $inView = true;
    public bool $isNotNull = false;

    public string $migrationText = '';
    public string $foreignKeyText = '';

    public int $numberDecimalPoints = 2;
    /** @var \Doctrine\DBAL\Schema\Column */
    public $fieldDetails = null;

    public function parseDBType(string $dbInput)
    {
        $dbInputArr = explode(':', $dbInput);
        $dbType = (string) array_shift($dbInputArr);

        if (Str::contains($dbType, ',')) {
            $dbTypeArr = explode(',', $dbType);
            $this->dbType = (string) array_shift($dbTypeArr);
            $this->dbTypeParams = $dbTypeArr;
        } else {
            $this->dbType = $dbType;
        }

        $this->dbExtraFunctions = $dbInputArr;

//        if (!is_null($column)) {
//            $this->dbType = ($column->getLength() > 0) ? $this->dbType.','.$column->getLength() : $this->dbType;
//            $this->dbType = (!$column->getNotnull()) ? $this->dbType.':nullable' : $this->dbType;
//        }

        $this->prepareMigrationText();
    }

    public function parseHtmlInput(string $htmlInput)
    {
        if (empty($htmlInput)) {
            $this->htmlType = 'text';

            return;
        }

        if (!Str::contains($htmlInput, ':')) {
            $this->htmlType = $htmlInput;

            return;
        }

        $htmlInputArr = explode(':', $htmlInput);
        $this->htmlType = (string) array_shift($htmlInputArr);
        $this->htmlValues = explode(',', implode(':', $htmlInputArr));
    }

    public function parseOptions(string $options)
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

    protected function prepareMigrationText()
    {
        $this->migrationText = '$table->';
        $this->migrationText .= $this->dbType."('".$this->name."'";

        if (!count($this->dbTypeParams) and !count($this->dbExtraFunctions)) {
            $this->migrationText .= ');';

            return;
        }

        if (count($this->dbTypeParams)) {
//        if ($this->dbType === 'enum') {
//            $this->migrationText .= ', [';
//            foreach ($fieldTypeParams as $param) {
//                $this->migrationText .= "'".$param."',";
//            }
//            $this->migrationText = substr($this->migrationText, 0, strlen($this->migrationText) - 1);
//            $this->migrationText .= ']';
//        }
            foreach ($this->dbTypeParams as $dbTypeParam) {
                $this->migrationText .= ', '.$dbTypeParam;
            }
        }

        $this->migrationText .= ')';

        if (!count($this->dbExtraFunctions)) {
            $this->migrationText .= ';';

            return;
        }

        $this->foreignKeyText = '';
        foreach ($this->dbExtraFunctions as $dbExtraFunction) {
            $dbExtraFunctionArr = explode(',', $dbExtraFunction);
            $functionName = (string) array_shift($dbExtraFunctionArr);
            if ($functionName === 'foreign') {
                $foreignTable = array_shift($dbExtraFunctionArr);
                $foreignField = array_shift($dbExtraFunctionArr);
                $this->foreignKeyText .= "\$table->foreign('".$this->name."')->references('".$foreignField."')->on('".$foreignTable."')";
                if (count($dbExtraFunctionArr)) {
                    $cascade = array_shift($dbExtraFunctionArr);
                    if ($cascade === 'cascade') {
                        $this->foreignKeyText .= "->onUpdate('cascade')->onDelete('cascade')";
                    }
                }
                $this->foreignKeyText .= ';';
            } else {
                $this->migrationText .= '->'.$functionName;
                $this->migrationText .= '(';
                $this->migrationText .= implode(', ', $dbExtraFunctionArr);
                $this->migrationText .= ')';
            }
        }

        $this->migrationText .= ';';
    }

    public static function parseFieldFromConsoleInput(string $fieldInput, string $validations = ''): self
    {
        /*
         * Field Input Format: field_name <space> db_type <space> html_type(optional) <space> options(optional)
         * Options are to skip the field from certain criteria like searchable, fillable, not in form, not in index
         * Searchable (s), Fillable (f), In Form (if), In Index (ii)
         * Sample Field Inputs
         *
         * title string text
         * body text textarea
         * name string,20 text
         * post_id integer:unsigned:nullable
         * post_id unsignedinteger:nullable:foreign,posts,id
         * password string text if,ii,s - options will skip field from being added in form, in index and searchable
         */

        $field = new self();
        $fieldInputArr = explode(' ', $fieldInput);
        $field->name = $fieldInputArr[0];

        $field->parseDBType($fieldInputArr[1]);

        $field->parseHtmlInput($fieldInputArr[2]);

        if (count($fieldInputArr) > 3) {
            $field->parseOptions($fieldInputArr[3]);
        }

        $field->validations = $validations;

        if (str_contains($field->validations, 'required')) {
            $field->isNotNull = true;
        }

        return $field;
    }

    public static function parseFieldFromFile(array $fieldInput): self
    {
        $field = new self();
        $field->name = $fieldInput['name'];
        $field->parseDBType($fieldInput['dbType']);
        $field->parseHtmlInput($fieldInput['htmlType'] ?? '');
        $field->validations = $fieldInput['validations'] ?? '';
        $field->isSearchable = $fieldInput['searchable'] ?? false;
        $field->isFillable = $fieldInput['fillable'] ?? true;
        $field->isPrimary = $fieldInput['primary'] ?? false;
        $field->inForm = $fieldInput['inForm'] ?? true;
        $field->inIndex = $fieldInput['inIndex'] ?? true;
        $field->inView = $fieldInput['inView'] ?? true;

        if (str_contains($field->validations, 'required')) {
            $field->isNotNull = true;
        }

        return $field;
    }

    public function getTitle(): string
    {
        return Str::title(str_replace('_', ' ', $this->name));
    }

    public function variables(): array
    {
        return [
            'fieldName'  => $this->name,
            'fieldTitle' => $this->getTitle(),
        ];
    }
}

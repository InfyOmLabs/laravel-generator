<?php

namespace InfyOm\Generator\Generators;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Utils\FileUtil;
use InfyOm\Generator\Utils\GeneratorFieldsInputUtil;
use InfyOm\Generator\Utils\TemplateUtil;

class TestTraitGenerator
{
    /** @var  CommandData */
    private $commandData;

    /** @var string */
    private $path;

    public function __construct($commandData)
    {
        $this->commandData = $commandData;
        $this->path = config('infyom.laravel_generator.path.test_trait', base_path('tests/traits/'));
    }

    public function generate()
    {
        $templateData = TemplateUtil::getTemplate('test.trait', 'laravel-generator');

        $templateData = $this->fillTemplate($templateData);

        $fileName = 'Make'.$this->commandData->modelName.'Trait.php';

        FileUtil::createFile($this->path, $fileName, $templateData);

        $this->commandData->commandObj->comment("\nTestTrait created: ");
        $this->commandData->commandObj->info($fileName);
    }

    private function fillTemplate($templateData)
    {
        $templateData = TemplateUtil::fillTemplate($this->commandData->dynamicVars, $templateData);

        $templateData = str_replace('$FIELDS$', implode(",\n\t\t\t", $this->generateFields()), $templateData);

        return $templateData;
    }

    private function generateFields()
    {
        $fields = [];

        foreach ($this->commandData->inputFields as $field) {
            $fieldData = "'".$field['fieldName']."' => ".'$fake->';

            switch ($field['fieldType']) {
                case 'integer':
                case 'float':
                    $fakerData = 'randomDigitNotNull';
                    break;
                case 'string':
                    $fakerData = 'word';
                    break;
                case 'text':
                    $fakerData = 'text';
                    break;
                case 'datetime':
                    $fakerData = "date('Y-m-d H:i:s')";
                    break;
                case 'enum':
                    $fakerData = 'randomElement('.GeneratorFieldsInputUtil::prepareValuesArrayStr(explode(',', $field['htmlTypeInputs'])).')';
                    break;
                default:
                    $fakerData = 'word';
            }

            $fieldData .= $fakerData;

            $fields[] = $fieldData;
        }

        return $fields;
    }
}

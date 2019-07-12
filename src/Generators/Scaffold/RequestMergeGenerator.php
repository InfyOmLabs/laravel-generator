<?php

namespace InfyOm\Generator\Generators\Scaffold;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\FileUtil;

class RequestMergeGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    /** @var string */
    private $requestFileName;
    
    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathRequest;
        $this->requestFileName = $this->commandData->modelName.'Request.php';
    }

    public function generate()
    {
        $this->generateCreateRequest();
    }

    private function generateCreateRequest()
    {
        $templateData = get_template('scaffold.request.merge_request', 'laravel-generator');
        
        $templateData = $this->fillTemplate($templateData);

        FileUtil::createFile($this->path, $this->requestFileName, $templateData);

        $this->commandData->commandComment("\nCreate Request created: ");
        $this->commandData->commandInfo($this->requestFileName);
    }

    private function fillTemplate($templateData)
    {
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $templateData = str_replace('$RULES$', implode(','.infy_nl_tab(1, 2), $this->generateRules('rules')), $templateData);
        $templateData = str_replace('$RULES_MESSAGES$', implode(','.infy_nl_tab(1, 3), $this->generateRules('messages')), $templateData);
        $templateData = str_replace('$RULES_ATTRIBUTES$', implode(','.infy_nl_tab(1, 3), $this->generateRules('attributes')), $templateData);

        return $templateData;
    }
    
    private function generateRules($type){
        $dont_require_fields = config('infyom.laravel_generator.options.hidden_fields', [])
            + config('infyom.laravel_generator.options.excluded_fields', []);

        $rules = [];

        foreach ($this->commandData->fields as $field) {
            if (!in_array($field->name, $dont_require_fields)) {
                $field->validations = 'required';
            }
            if (!empty($field->validations)) {
                if($type === 'rules'){
                    $rule = "'".$field->name."' => '".$field->validations."'";
                } else if($type === 'messages'){
                    $rule = "'".$field->name.".required' => 'กรุณาระบุ :attribute'";
                } else if($type === 'attributes'){
                    $rule = "'".$field->name."' => '".($field->description ?? $field->name)."'";
                }

                $rules[] = $rule;
            }
        }

        return $rules;
    }
}

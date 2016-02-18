<?php

namespace InfyOm\Generator\Commands;

class PublishTemplateCommand extends PublishBaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'infyom.publish:templates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishes api generator templates.';

    private $templatesDir;

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $this->templatesDir = config(
            'infyom.laravel_generator.path.templates_dir',
            base_path('resources/infyom/infyom-generator-templates/')
        );

        if ($this->publishGeneratorTemplates()) {
            $this->publishScaffoldTemplates();
        }
    }

    /**
     * Publishes templates.
     */
    public function publishGeneratorTemplates()
    {
        $templatesPath = __DIR__.'/../../templates';

        return $this->publishDirectory($templatesPath, $this->templatesDir, 'infyom-generator-templates');
    }

    /**
     * Publishes templates.
     */
    public function publishScaffoldTemplates()
    {
        $templateType = config('infyom.laravel_generator.templates', 'core-templates');

        $basePath = base_path('vendor/infyom/'.$templateType);

        if (!file_exists($basePath)) {
            $basePath = base_path('packages/infyom/'.$templateType);
        }

        $templatesPath = $basePath.'/templates';

        return $this->publishDirectory($templatesPath, $this->templatesDir, 'infyom-generator-templates', true);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return [];
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }
}

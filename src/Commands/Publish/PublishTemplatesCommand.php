<?php

namespace InfyOm\Generator\Commands\Publish;

class PublishTemplatesCommand extends PublishBaseCommand
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

    private string $templatesDir;

    public function handle()
    {
        $this->templatesDir = config(
            'laravel_generator.path.templates_dir',
            resource_path('infyom/infyom-generator-templates/')
        );

        if ($this->publishGeneratorTemplates()) {
            $this->publishScaffoldTemplates();
            $this->publishSwaggerTemplates();
        }
    }

    public function publishGeneratorTemplates(): bool
    {
        $templatesPath = __DIR__ . '/../../../views';

        return $this->publishDirectory($templatesPath, $this->templatesDir, 'infyom-generator-templates');
    }

    public function publishScaffoldTemplates(): bool
    {
        $templateType = config('laravel_generator.templates', 'adminlte-templates');

        $templatesPath = get_templates_package_path($templateType).'/templates/scaffold';

        return $this->publishDirectory($templatesPath, $this->templatesDir.'scaffold', 'infyom-generator-templates/scaffold', true);
    }

    public function publishSwaggerTemplates(): bool
    {
        $templatesPath = base_path('vendor/infyomlabs/swagger-generator/templates');

        return $this->publishDirectory($templatesPath, $this->templatesDir, 'swagger-generator', true);
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

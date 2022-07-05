<?php

namespace InfyOm\Generator\Commands\Publish;

class PublishTemplatesCommand extends PublishBaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'infyom.publish:stubs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishes api generator stubs.';

    private string $templatesDir;

    public function handle()
    {
        $this->templatesDir = config(
            'laravel_generator.path.templates_dir',
            resource_path('infyom/infyom-generator-stubs/')
        );

        if ($this->publishGeneratorTemplates()) {
            $this->publishScaffoldTemplates();
            $this->publishSwaggerTemplates();
        }
    }

    public function publishGeneratorTemplates(): bool
    {
        $templatesPath = __DIR__ . '/../../../stubs';

        return $this->publishDirectory($templatesPath, $this->templatesDir, 'infyom-generator-stubs');
    }

    public function publishScaffoldTemplates(): bool
    {
        $templateType = config('laravel_generator.stubs', 'adminlte-stubs');

        $templatesPath = get_templates_package_path($templateType).'/stubs/scaffold';

        return $this->publishDirectory($templatesPath, $this->templatesDir.'scaffold', 'infyom-generator-stubs/scaffold', true);
    }

    public function publishSwaggerTemplates(): bool
    {
        $templatesPath = base_path('vendor/infyomlabs/swagger-generator/stubs');

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

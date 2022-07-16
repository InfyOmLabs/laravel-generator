<?php

namespace InfyOm\Generator\Commands\Publish;

use Exception;
use Symfony\Component\Console\Input\InputArgument;

class PublishTablesCommand extends PublishBaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'infyom.publish:tables';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishes Table files';

    public function handle()
    {
        $tableType = $this->argument('type');

        if ($tableType === 'datatable') {
            $this->publishDataTableViews();

            return;
        }

        if ($tableType === 'livewire') {
            $this->publishLivewireTableViews();

            return;
        }

        throw new Exception('Invalid Table Type');
    }

    public function publishLivewireTableViews()
    {
        $viewsPath = config('laravel_generator.path.views', resource_path('views/'));
        $templateType = config('infyom.laravel_generator.templates', 'adminlte-templates');
        $files = [
            'views/templates/scaffold/table/livewire/actions.blade.php' => 'common/livewire-tables/actions.blade.php',
        ];

        g_filesystem()->createDirectoryIfNotExist($viewsPath.'common/livewire-tables');

        foreach ($files as $stub => $blade) {
            $sourceFile = get_templates_package_path($templateType).'/'.$stub;
            $destinationFile = $viewsPath.$blade;
            $this->publishFile($sourceFile, $destinationFile, $blade);
        }
    }

    protected function publishDataTableViews()
    {
        $viewsPath = config('laravel_generator.path.views', resource_path('views/'));

        $files = [
            'layouts/datatables_css'    => 'layouts/datatables_css.blade.php',
            'layouts/datatables_js'     => 'layouts/datatables_js.blade.php',
        ];

        foreach ($files as $stub => $blade) {
            $sourceFile = get_template_file_path('views/scaffold/'.$stub, 'laravel-generator');
            $destinationFile = $viewsPath.$blade;
            $this->publishFile($sourceFile, $destinationFile, $blade);
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    public function getArguments()
    {
        return [
            ['type', InputArgument::REQUIRED, 'Types of Tables (datatable / livewire)'],
        ];
    }
}

<?php

namespace InfyOm\Generator\Commands\Publish;

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

        throw new \Exception('Invalid Table Type');
    }

    public function publishLivewireTableViews()
    {
        $viewsPath = config('laravel_generator.path.views', resource_path('views/'));
        $templateType = config('infyom.laravel_generator.templates', 'adminlte-templates');

        $files = [
            'views/table/livewire/actions' => 'common/livewire-tables/actions.blade.php',
        ];

        foreach ($files as $stub => $blade) {
            $sourceFile = get_template_file_path('scaffold/'.$stub, $templateType);
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
            $sourceFile = get_template_file_path('scaffold/'.$stub, 'laravel-generator');
            $destinationFile = $viewsPath.$blade;
            $this->publishFile($sourceFile, $destinationFile, $blade);
        }
    }

    public function getArguments()
    {
        return [
            ['type', InputArgument::REQUIRED, 'Types of Tables (datatable / livewire)'],
        ];
    }
}

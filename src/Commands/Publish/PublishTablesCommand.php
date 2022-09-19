<?php

namespace InfyOm\Generator\Commands\Publish;

use Exception;
use Illuminate\View\Factory;
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
        $templateType = config('laravel_generator.templates', 'adminlte-templates');
        $files = [
            'templates.scaffold.table.livewire.actions' => 'common/livewire-tables/actions.blade.php',
        ];

        g_filesystem()->createDirectoryIfNotExist($viewsPath.'common/livewire-tables');

        /** @var Factory $viewFactory */
        $viewFactory = view();
        foreach ($files as $templateView => $destinationView) {
            $templateViewPath = $viewFactory->getFinder()->find($templateType.'::'.$templateView);
            $content = g_filesystem()->getFile($templateViewPath);
            $destinationFile = $viewsPath.$destinationView;
            g_filesystem()->createFile($destinationFile, $content);
        }
    }

    protected function publishDataTableViews()
    {
        $viewsPath = config('laravel_generator.path.views', resource_path('views/'));

        $files = [
            'layouts.datatables_css' => 'layouts/datatables_css.blade.php',
            'layouts.datatables_js'  => 'layouts/datatables_js.blade.php',
        ];

        foreach ($files as $templateView => $destinationView) {
            $content = view('laravel-generator::scaffold.'.$templateView)->render();
            $destinationFile = $viewsPath.$destinationView;
            g_filesystem()->createFile($destinationFile, $content);
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

<?php

namespace InfyOm\Generator\Commands\Publish;

class PublishDataTablesCommand extends PublishBaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'infyom.publish:datatables';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishes DataTable files';

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
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
}

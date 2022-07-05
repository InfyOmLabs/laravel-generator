@php
    echo "<?php".PHP_EOL;
@endphp

namespace {{ $config->namespaces->livewireTables }};

use Laracasts\Flash\Flash;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use {{ $config->namespaces->model }}\{{ $config->modelNames->name }};

class {{ $config->modelNames->plural }}Table extends DataTableComponent
{
    protected $model = {{ $config->modelNames->name }}::class;

    protected $listeners = ['deleteRecord' => 'deleteRecord'];

    public function deleteRecord($id)
    {
        {{ $config->modelNames->name }}::find($id)->delete();
@if($config->options->localized)
        Flash::success(__('messages.deleted', ['model' => __('models/{{ $config->modelNames->camelPlural }}.singular')]));
@else
        Flash::success('{{ $config->modelNames->human }} deleted successfully.');
@endif
        $this->emit('refreshDatatable');
    }

    public function configure(): void
    {
        $this->setPrimaryKey('{{ $config->primaryName }}');
    }

    public function columns(): array
    {
        return [
            {!! $columns !!},
            Column::make("Actions", '{{ $config->primaryName }}')
                ->format(
                    fn($value, $row, Column $column) => view('common.livewire-tables.actions', [
                        'showUrl' => route('{{ $config->modelNames->dashedPlural }}.show', $row->{{ $config->primaryName }}),
                        'editUrl' => route('{{ $config->modelNames->dashedPlural }}.edit', $row->{{ $config->primaryName }}),
                        'recordId' => $row->{{ $config->primaryName }},
                    ])
                )
        ];
    }
}

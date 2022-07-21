@if($config->options->localized)
    Flash::success(__('messages.deleted', ['model' => __('models/{{ $config->modelNames->camelPlural }}.singular')]));
@else
    Flash::success('{{ $config->modelNames->human }} deleted successfully.');
@endif
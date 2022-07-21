@if($config->options->localized)
    Flash::success(__('messages.updated', ['model' => __('models/{{ $config->modelNames->camelPlural }}.singular')]));
@else
    Flash::success('{{ $config->modelNames->human }} updated successfully.');
@endif
@php
    echo "<?php".PHP_EOL;
@endphp

namespace {{ $config->namespaces->apiController }};

use {{ $config->namespaces->apiRequest }}\Create{{ $config->modelNames->name }}APIRequest;
use {{ $config->namespaces->apiRequest }}\Update{{ $config->modelNames->name }}APIRequest;
use {{ $config->namespaces->model }}\{{ $config->modelNames->name }};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use {{ $config->namespaces->app }}\Http\Controllers\AppBaseController;

{!! $docController !!}
class {{ $config->modelNames->name }}APIController extends AppBaseController
{
    {!! $docIndex !!}
    public function index(Request $request): JsonResponse
    {
        $query = {{ $config->modelNames->name }}::query();

        if ($request->get('skip')) {
            $query->skip($request->get('skip'));
        }
        if ($request->get('limit')) {
            $query->limit($request->get('limit'));
        }

        ${{ $config->modelNames->camelPlural }} = $query->get();

@if($config->options->localized)
        return $this->sendResponse(
            ${{ $config->modelNames->camelPlural }}->toArray(),
            __('messages.retrieved', ['model' => __('models/{{ $config->modelNames->camelPlural }}.plural')])
        );
@else
        return $this->sendResponse(${{ $config->modelNames->camelPlural }}->toArray(), '{{ $config->modelNames->humanPlural }} retrieved successfully');
@endif
    }

    {!! $docStore !!}
    public function store(Create{{ $config->modelNames->name }}APIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var {{ $config->modelNames->name }} ${{ $config->modelNames->camel }} */
        ${{ $config->modelNames->camel }} = {{ $config->modelNames->name }}::create($input);

@if($config->options->localized)
        return $this->sendResponse(
            ${{ $config->modelNames->camel }}->toArray(),
            __('messages.saved', ['model' => __('models/{{ $config->modelNames->camelPlural }}.singular')])
        );
@else
        return $this->sendResponse(${{ $config->modelNames->camel }}->toArray(), '{{ $config->modelNames->human }} saved successfully');
@endif
    }

    {!! $docShow !!}
    public function show({{ $config->modelNames->name }} ${{ $config->modelNames->camel }}): JsonResponse
    {
        if (empty(${{ $config->modelNames->camel }})) {
@if($config->options->localized)
            return $this->sendError(
                __('messages.not_found', ['model' => __('models/{{ $config->modelNames->camelPlural }}.singular')])
            );
@else
            return $this->sendError('{{ $config->modelNames->human }} not found');
@endif
        }

@if($config->options->localized)
        return $this->sendResponse(
            ${{ $config->modelNames->camel }}->toArray(),
            __('messages.retrieved', ['model' => __('models/{{ $config->modelNames->camelPlural }}.singular')])
        );
@else
        return $this->sendResponse(${{ $config->modelNames->camel }}->toArray(), '{{ $config->modelNames->human }} retrieved successfully');
@endif
    }

    {!! $docUpdate !!}
    public function update({{ $config->modelNames->name }} ${{ $config->modelNames->camel }}, Update{{ $config->modelNames->name }}APIRequest $request): JsonResponse
    {
        if (empty(${{ $config->modelNames->camel }})) {
@if($config->options->localized)
            return $this->sendError(
            __('messages.not_found', ['model' => __('models/{{ $config->modelNames->camelPlural }}.singular')])
            );
@else
            return $this->sendError('{{ $config->modelNames->human }} not found');
@endif
        }

        ${{ $config->modelNames->camel }}->fill($request->all());
        ${{ $config->modelNames->camel }}->save();

@if($config->options->localized)
        return $this->sendResponse(
            ${{ $config->modelNames->camel }}->toArray(),
            __('messages.updated', ['model' => __('models/{{ $config->modelNames->camelPlural }}.singular')])
        );
@else
        return $this->sendResponse(${{ $config->modelNames->camel }}->toArray(), '{{ $config->modelNames->name }} updated successfully');
@endif
    }

    {!! $docDestroy !!}
    public function destroy({{ $config->modelNames->name }} ${{ $config->modelNames->camel }}): JsonResponse
    {
        if (empty(${{ $config->modelNames->camel }})) {
@if($config->options->localized)
            return $this->sendError(
                __('messages.not_found', ['model' => __('models/{{ $config->modelNames->camelPlural }}.singular')])
            );
@else
            return $this->sendError('{{ $config->modelNames->human }} not found');
@endif
        }

        ${{ $config->modelNames->camel }}->delete();

@if($config->options->localized)
        return $this->sendResponse(
            ${{ $config->modelNames->camel }}->id,
            __('messages.deleted', ['model' => __('models/{{ $config->modelNames->camelPlural }}.singular')])
        );
@else
        return $this->sendSuccess('{{ $config->modelNames->human }} deleted successfully');
@endif
    }
}

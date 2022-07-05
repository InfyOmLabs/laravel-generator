@php
    echo "<?php".PHP_EOL;
@endphp

namespace {{ $config->namespaces->controller }};

use {{ $config->namespaces->dataTables }}\{{ $config->modelNames->name }}DataTable;
use {{ $config->namespaces->request }}\Create{{ $config->modelNames->name }}Request;
use {{ $config->namespaces->request }}\Update{{ $config->modelNames->name }}Request;
use {{ $config->namespaces->app }}\Http\Controllers\AppBaseController;
use {{ $config->namespaces->repository }}\{{ $config->modelNames->name }}Repository;
use Flash;

class {{ $config->modelNames->name }}Controller extends AppBaseController
{
    /** @var {{ $config->modelNames->name }}Repository ${{ $config->modelNames->camel }}Repository*/
    private ${{ $config->modelNames->camel }}Repository;

    public function __construct({{ $config->modelNames->name }}Repository ${{ $config->modelNames->camel }}Repo)
    {
        $this->{{ $config->modelNames->camel }}Repository = ${{ $config->modelNames->camel }}Repo;
    }

    /**
     * Display a listing of the {{ $config->modelNames->name }}.
     */
    public function index({{ $config->modelNames->name }}DataTable ${{ $config->modelNames->camel }}DataTable)
    {
        return ${{ $config->modelNames->camel }}DataTable->render('{{ $config->modelNames->snakePlural }}.index');
    }

    /**
     * Show the form for creating a new {{ $config->modelNames->name }}.
     */
    public function create()
    {
        return view('{{ $config->modelNames->snakePlural }}.create');
    }

    /**
     * Store a newly created {{ $config->modelNames->name }} in storage.
     */
    public function store(Create{{ $config->modelNames->name }}Request $request)
    {
        $input = $request->all();

        ${{ $config->modelNames->camel }} = $this->{{ $config->modelNames->camel }}Repository->create($input);

@if($config->options->localized)
        Flash::success(__('messages.saved', ['model' => __('models/{{ $config->modelNames->camelPlural }}.singular')]));
@else
        Flash::success('{{ $config->modelNames->human }} saved successfully.');
@endif

        return redirect(route('{{ $config->modelNames->camelPlural }}.index'));
    }

    /**
     * Display the specified {{ $config->modelNames->name }}.
     */
    public function show($id)
    {
        ${{ $config->modelNames->camel }} = $this->{{ $config->modelNames->camel }}Repository->find($id);

        if (empty(${{ $config->modelNames->camel }})) {
@if($config->options->localized)
            Flash::error(__('models/{{ $config->modelNames->camelPlural }}.singular').' '.__('messages.not_found'));
@else
            Flash::error('{{ $config->modelNames->human }} not found');
@endif

            return redirect(route('{{ $config->modelNames->camelPlural }}.index'));
        }

        return view('{{ $config->modelNames->snakePlural }}.show')->with('{{ $config->modelNames->camel }}', ${{ $config->modelNames->camel }});
    }

    /**
     * Show the form for editing the specified {{ $config->modelNames->name }}.
     */
    public function edit($id)
    {
        ${{ $config->modelNames->camel }} = $this->{{ $config->modelNames->camel }}Repository->find($id);

        if (empty(${{ $config->modelNames->camel }})) {
@if($config->options->localized)
            Flash::error(__('models/{{ $config->modelNames->camelPlural }}.singular').' '.__('messages.not_found'));
@else
            Flash::error('{{ $config->modelNames->human }} not found');
@endif

            return redirect(route('{{ $config->modelNames->camelPlural }}.index'));
        }

        return view('{{ $config->modelNames->snakePlural }}.edit')->with('{{ $config->modelNames->camel }}', ${{ $config->modelNames->camel }});
    }

    /**
     * Update the specified {{ $config->modelNames->name }} in storage.
     */
    public function update($id, Update{{ $config->modelNames->name }}Request $request)
    {
        ${{ $config->modelNames->camel }} = $this->{{ $config->modelNames->camel }}Repository->find($id);

        if (empty(${{ $config->modelNames->camel }})) {
@if($config->options->localized)
            Flash::error(__('models/{{ $config->modelNames->camelPlural }}.singular').' '.__('messages.not_found'));
@else
            Flash::error('{{ $config->modelNames->human }} not found');
@endif

            return redirect(route('{{ $config->modelNames->camelPlural }}.index'));
        }

        ${{ $config->modelNames->camel }} = $this->{{ $config->modelNames->camel }}Repository->update($request->all(), $id);

@if($config->options->localized)
        Flash::success(__('messages.updated', ['model' => __('models/{{ $config->modelNames->camelPlural }}.singular')]));
@else
        Flash::success('{{ $config->modelNames->human }} updated successfully.');
@endif

        return redirect(route('{{ $config->modelNames->camelPlural }}.index'));
    }

    /**
     * Remove the specified {{ $config->modelNames->name }} from storage.
     */
    public function destroy($id)
    {
        ${{ $config->modelNames->camel }} = $this->{{ $config->modelNames->camel }}Repository->find($id);

        if (empty(${{ $config->modelNames->camel }})) {
@if($config->options->localized)
            Flash::error(__('models/{{ $config->modelNames->camelPlural }}.singular').' '.__('messages.not_found'));
@else
            Flash::error('{{ $config->modelNames->human }} not found');
@endif

            return redirect(route('{{ $config->modelNames->camelPlural }}.index'));
        }

        $this->{{ $config->modelNames->camel }}Repository->delete($id);

@if($config->options->localized)
        Flash::success(__('messages.deleted', ['model' => __('models/{{ $config->modelNames->camelPlural }}.singular')]));
@else
        Flash::success('{{ $config->modelNames->human }} deleted successfully.');
@endif

        return redirect(route('{{ $config->modelNames->camelPlural }}.index'));
    }
}

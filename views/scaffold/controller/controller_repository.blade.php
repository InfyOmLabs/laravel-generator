@php
    echo "<?php".PHP_EOL;
@endphp

namespace {{ $config->namespaces->controller }};

@if(config('laravel_generator.tables') === 'datatables')
use {{ $config->namespaces->dataTables }}\{{ $config->modelNames->name }}DataTable;
@endif
use {{ $config->namespaces->request }}\Create{{ $config->modelNames->name }}Request;
use {{ $config->namespaces->request }}\Update{{ $config->modelNames->name }}Request;
use {{ $config->namespaces->app }}\Http\Controllers\AppBaseController;
use {{ $config->namespaces->repository }}\{{ $config->modelNames->name }}Repository;
use Illuminate\Http\Request;
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
    {!! $indexMethod !!}

    /**
     * Show the form for creating a new {{ $config->modelNames->name }}.
     */
    public function create()
    {
        return view('{{ $config->prefixes->getViewPrefixForInclude() }}{{ $config->modelNames->snakePlural }}.create');
    }

    /**
     * Store a newly created {{ $config->modelNames->name }} in storage.
     */
    public function store(Create{{ $config->modelNames->name }}Request $request)
    {
        $input = $request->all();

        ${{ $config->modelNames->camel }} = $this->{{ $config->modelNames->camel }}Repository->create($input);

        @include('laravel-generator::scaffold.controller.messages.save_success')

        return redirect(route('{{ $config->prefixes->getRoutePrefixWith('.') }}{{ $config->modelNames->camelPlural }}.index'));
    }

    /**
     * Display the specified {{ $config->modelNames->name }}.
     */
    public function show($id)
    {
        ${{ $config->modelNames->camel }} = $this->{{ $config->modelNames->camel }}Repository->find($id);

        @include('laravel-generator::scaffold.controller.messages.not_found')

        return view('{{ $config->prefixes->getViewPrefixForInclude() }}{{ $config->modelNames->snakePlural }}.show')->with('{{ $config->modelNames->camel }}', ${{ $config->modelNames->camel }});
    }

    /**
     * Show the form for editing the specified {{ $config->modelNames->name }}.
     */
    public function edit($id)
    {
        ${{ $config->modelNames->camel }} = $this->{{ $config->modelNames->camel }}Repository->find($id);

        @include('laravel-generator::scaffold.controller.messages.not_found')

        return view('{{ $config->prefixes->getViewPrefixForInclude() }}{{ $config->modelNames->snakePlural }}.edit')->with('{{ $config->modelNames->camel }}', ${{ $config->modelNames->camel }});
    }

    /**
     * Update the specified {{ $config->modelNames->name }} in storage.
     */
    public function update($id, Update{{ $config->modelNames->name }}Request $request)
    {
        ${{ $config->modelNames->camel }} = $this->{{ $config->modelNames->camel }}Repository->find($id);

        @include('laravel-generator::scaffold.controller.messages.not_found')

        ${{ $config->modelNames->camel }} = $this->{{ $config->modelNames->camel }}Repository->update($request->all(), $id);

        @include('laravel-generator::scaffold.controller.messages.update_success')

        return redirect(route('{{ $config->prefixes->getRoutePrefixWith('.') }}{{ $config->modelNames->camelPlural }}.index'));
    }

    /**
     * Remove the specified {{ $config->modelNames->name }} from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        ${{ $config->modelNames->camel }} = $this->{{ $config->modelNames->camel }}Repository->find($id);

        @include('laravel-generator::scaffold.controller.messages.not_found')

        $this->{{ $config->modelNames->camel }}Repository->delete($id);

        @include('laravel-generator::scaffold.controller.messages.delete_success')

        return redirect(route('{{ $config->prefixes->getRoutePrefixWith('.') }}{{ $config->modelNames->camelPlural }}.index'));
    }
}

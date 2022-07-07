    public function index(Request $request)
    {
        /** @var {{ $config->modelNames->name }} ${{ $config->modelNames->camelPlural }} */
        ${{ $config->modelNames->camelPlural }} = {{ $config->modelNames->name }}::{!! $renderType !!};

        return view('{{ $config->prefixes->getViewPrefixForInclude() }}{{ $config->modelNames->snakePlural }}.index')
            ->with('{{ $config->modelNames->camelPlural }}', ${{ $config->modelNames->camelPlural }});
    }

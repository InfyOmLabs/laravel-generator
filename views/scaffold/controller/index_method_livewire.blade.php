    public function index(Request $request)
    {
        return view('{{ $config->modelNames->snakePlural }}.index');
    }
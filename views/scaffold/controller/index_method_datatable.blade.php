    public function index({{ $config->modelNames->name }}DataTable ${{ $config->modelNames->camel }}DataTable): Response
    {
    return ${{ $config->modelNames->camel }}DataTable->render('{{ $config->modelNames->snakePlural }}.index');
    }
    public function {{ $functionName }}(): \Illuminate\Database\Eloquent\Relations\{{ $relationClass }}
    {
        return $this->{{ $relation }}(\{{ $config->namespaces->model }}\{{ $relatedModel }}::class{!! $fields !!});
    }
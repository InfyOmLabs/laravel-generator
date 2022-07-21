@php
    echo "<?php".PHP_EOL;
@endphp

namespace {{ $config->namespaces->factory }};

use {{ $config->namespaces->model }}\{{ $config->modelNames->name }};
use Illuminate\Database\Eloquent\Factories\Factory;
{!! $usedRelations !!}

class {{ $config->modelNames->name }}Factory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = {{ $config->modelNames->name }}::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        {!! $relations !!}
        return [
            {!! $fields !!}
        ];
    }
}

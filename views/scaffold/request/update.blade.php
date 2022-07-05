@php
    echo "<?php".PHP_EOL;
@endphp

namespace {{ $config->namespaces->request }};

use {{ $config->namespaces->model }}\{{ $config->modelNames->name }};
use Illuminate\Foundation\Http\FormRequest;

class Update{{ $config->modelNames->name }}Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = {{ $config->modelNames->name }}::$rules;
        {!! $uniqueRules !!}
        return $rules;
    }
}

@php
    echo "<?php".PHP_EOL;
@endphp

namespace {{ config('laravel_generator.namespace.request') }};

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
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
       $rules = [
          'name'                  => 'required',
          'email'                 => 'required|email|unique:users,email',
          'password'              => 'required|confirmed'
       ];

        return $rules;
    }
}

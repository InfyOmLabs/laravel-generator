<?php

namespace App\Http\Requests\Profile;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation labels that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        $rules_labels = User::$labels;
        return $rules_labels;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $rules = User::$standard_rules;
        $rules['email'][] = Rule::unique((new User)->getTable())->ignore(auth()->id());

        return $rules;
    }
}

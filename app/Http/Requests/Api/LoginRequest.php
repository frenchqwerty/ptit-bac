<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<string>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:50'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'Un prénom est requis pour jouer.',
            'name.min' => 'Ton prénom doit avoir au moins 2 caractères.',
            'name.max' => 'Ton prénom ne peut pas dépasser 50 caractères.',
        ];
    }
}

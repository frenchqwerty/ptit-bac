<?php

namespace App\Http\Requests\Api;

use App\Domain\Game\Enums\Category;
use Illuminate\Foundation\Http\FormRequest;

class SubmitVotesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $categories = Category::values();
        $rules = ['votes' => ['required', 'array']];

        foreach ($categories as $category) {
            $rules["votes.*.{$category}"] = ['sometimes', 'boolean'];
        }

        return $rules;
    }
}

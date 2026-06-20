<?php

namespace App\Http\Requests\Api;

use App\Domain\Game\Enums\Category;
use Illuminate\Foundation\Http\FormRequest;

class SubmitAnswersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $categories = Category::values();
        $rules = [];

        foreach ($categories as $category) {
            $rules["answers.{$category}"] = ['sometimes', 'nullable', 'string', 'max:100'];
        }

        return $rules;
    }
}

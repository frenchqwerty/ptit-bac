<?php

namespace App\Http\Requests\Api;

use App\Domain\Game\Enums\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateGameRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'total_rounds' => ['sometimes', 'integer', 'min:1', 'max:10'],
            'round_duration' => ['sometimes', 'integer', 'min:30', 'max:300'],
            'max_players' => ['sometimes', 'integer', 'min:2', 'max:10'],
            'categories' => ['sometimes', 'array', 'min:1'],
            'categories.*' => ['string', Rule::in(Category::values())],
        ];
    }
}

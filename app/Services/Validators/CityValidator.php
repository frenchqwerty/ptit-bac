<?php

namespace App\Services\Validators;

use App\Domain\Game\Enums\Category;

class CityValidator extends BaseValidator
{
    public function getCategoryKey(): string
    {
        return Category::City->value;
    }

    protected function isValidEntry(string $answer, string $letter): bool
    {
        $cities = $this->loadWordList('cities.json');

        if (! empty($cities)) {
            return in_array(mb_strtolower($answer), $cities, true);
        }

        // Fallback: basic heuristic — minimum 2 chars, no digits
        return mb_strlen($answer) >= 2 && ! preg_match('/\d/', $answer);
    }
}

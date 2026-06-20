<?php

namespace App\Services\Validators;

use App\Domain\Game\Enums\Category;

class CountryValidator extends BaseValidator
{
    public function getCategoryKey(): string
    {
        return Category::Country->value;
    }

    protected function isValidEntry(string $answer, string $letter): bool
    {
        $countries = $this->loadWordList('countries.json');

        if (! empty($countries)) {
            return in_array(mb_strtolower($answer), $countries, true);
        }

        return mb_strlen($answer) >= 3 && ! preg_match('/\d/', $answer);
    }
}

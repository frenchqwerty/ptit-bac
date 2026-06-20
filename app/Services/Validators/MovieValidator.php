<?php

namespace App\Services\Validators;

use App\Domain\Game\Enums\Category;

class MovieValidator extends BaseValidator
{
    public function getCategoryKey(): string
    {
        return Category::Movie->value;
    }

    protected function isValidEntry(string $answer, string $letter): bool
    {
        $movies = $this->loadWordList('movies.json');

        if (! empty($movies)) {
            return in_array(mb_strtolower($answer), $movies, true);
        }

        return mb_strlen($answer) >= 2;
    }
}

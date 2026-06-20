<?php

namespace App\Services\Validators;

use App\Domain\Game\Enums\Category;

class PokemonValidator extends BaseValidator
{
    public function getCategoryKey(): string
    {
        return Category::Pokemon->value;
    }

    protected function isValidEntry(string $answer, string $letter): bool
    {
        $pokemon = $this->loadWordList('pokemon.json');

        if (! empty($pokemon)) {
            return in_array(mb_strtolower($answer), $pokemon, true);
        }

        // Fallback heuristic
        return mb_strlen($answer) >= 2 && ! preg_match('/\d/', $answer);
    }
}

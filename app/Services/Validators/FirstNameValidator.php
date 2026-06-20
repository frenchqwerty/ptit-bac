<?php

namespace App\Services\Validators;

use App\Domain\Game\Enums\Category;

class FirstNameValidator extends BaseValidator
{
    public function getCategoryKey(): string
    {
        return Category::FirstName->value;
    }

    protected function isValidEntry(string $answer, string $letter): bool
    {
        $names = $this->loadWordList('first_names.json');

        if (! empty($names)) {
            return in_array(mb_strtolower($answer), $names, true);
        }

        return mb_strlen($answer) >= 2
            && ! preg_match('/\d/', $answer)
            && preg_match('/^[\p{L}\s\-\']+$/u', $answer);
    }
}

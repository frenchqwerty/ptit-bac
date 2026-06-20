<?php

namespace App\Services\Validators;

use App\Domain\Game\Enums\Category;

class BrandValidator extends BaseValidator
{
    public function getCategoryKey(): string
    {
        return Category::Brand->value;
    }

    protected function isValidEntry(string $answer, string $letter): bool
    {
        $brands = $this->loadWordList('brands.json');

        if (! empty($brands)) {
            return in_array(mb_strtolower($answer), $brands, true);
        }

        return mb_strlen($answer) >= 2;
    }
}

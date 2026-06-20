<?php

namespace App\Services\Contracts;

interface CategoryValidatorInterface
{
    /**
     * Validate that the given answer is valid for this category and starts with the given letter.
     */
    public function validate(string $answer, string $letter): bool;

    /**
     * Return the category key (matches Category enum value).
     */
    public function getCategoryKey(): string;
}

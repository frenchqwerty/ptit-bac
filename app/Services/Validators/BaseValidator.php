<?php

namespace App\Services\Validators;

use App\Services\Contracts\CategoryValidatorInterface;

abstract class BaseValidator implements CategoryValidatorInterface
{
    /**
     * Check the answer starts with (case-insensitive) the given letter,
     * then delegate to the concrete validator.
     */
    final public function validate(string $answer, string $letter): bool
    {
        $answer = trim($answer);

        if (empty($answer)) {
            return false;
        }

        if (mb_strtolower(mb_substr($answer, 0, 1)) !== mb_strtolower($letter)) {
            return false;
        }

        return $this->isValidEntry($answer, $letter);
    }

    /**
     * Concrete validators implement their specific logic here.
     * The answer is guaranteed non-empty and starts with $letter.
     */
    abstract protected function isValidEntry(string $answer, string $letter): bool;

    /**
     * Load a JSON word list from the data directory.
     *
     * @return array<string>
     */
    protected function loadWordList(string $filename): array
    {
        $path = base_path("data/{$filename}");

        if (! file_exists($path)) {
            return [];
        }

        /** @var array<string> $words */
        $words = json_decode(file_get_contents($path) ?: '[]', true) ?? [];

        return array_map('mb_strtolower', $words);
    }
}

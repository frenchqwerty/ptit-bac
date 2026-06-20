<?php

namespace App\Domain\Score\Services;

use App\Domain\Game\Enums\Category;
use App\Services\Contracts\CategoryValidatorInterface;

/**
 * Calculates scores for a round based on player answers.
 *
 * Point rules:
 *   - Unique valid answer: +10
 *   - Shared valid answer: +5
 *   - Empty or invalid:    +0
 *   - Perfect round bonus: +20 (all categories valid)
 */
final class ScoreEngine
{
    private const UNIQUE_POINTS = 10;

    private const SHARED_POINTS = 5;

    private const PERFECT_BONUS = 20;

    /** @var array<string, CategoryValidatorInterface> */
    private array $validators = [];

    /** @param array<CategoryValidatorInterface> $validators */
    public function __construct(array $validators = [])
    {
        foreach ($validators as $validator) {
            $this->validators[$validator->getCategoryKey()] = $validator;
        }
    }

    /**
     * Calculate scores for all players in a round.
     *
     * @param  array<string, array<string, string>>  $playerAnswers  {playerId: {category: answer}}
     * @param  string  $letter  The round letter
     * @return array<string, array{round_score: int, unique_count: int, has_perfect: bool, categories: array<string, array{value: string, valid: bool, unique: bool, points: int}>}>
     */
    /**
     * @param  array<string, array<string, string>>  $playerAnswers  {playerId: {category: answer}}
     * @param  string  $letter  The round letter
     * @param  array<int, array<string, bool>>  $manualValidity  {playerId: {category: isValid}} from player votes
     * @return array<string, array{round_score: int, unique_count: int, has_perfect: bool, categories: array<string, array{value: string, valid: bool, unique: bool, points: int}>}>
     */
    public function calculate(array $playerAnswers, string $letter, array $manualValidity = []): array
    {
        $categories = Category::values();
        $results = [];

        // Step 1 — determine validity and count occurrences per category
        /** @var array<string, array<string, bool>> $validity {playerId: {category: bool}} */
        $validity = [];

        /** @var array<string, array<string, int>> $occurrences {category: {normalized_answer: count}} */
        $occurrences = [];

        foreach ($categories as $category) {
            foreach ($playerAnswers as $playerId => $answers) {
                $raw = trim($answers[$category] ?? '');
                $normalized = mb_strtolower($raw);

                // Manual vote overrides auto-validation when present
                $isValid = isset($manualValidity[(int) $playerId][$category])
                    ? $manualValidity[(int) $playerId][$category]
                    : (! empty($raw) && $this->validateAnswer($raw, $letter, $category));

                $validity[$playerId][$category] = $isValid;

                if ($isValid) {
                    $occurrences[$category][$normalized] = ($occurrences[$category][$normalized] ?? 0) + 1;
                }
            }
        }

        // Step 2 — compute points per player
        foreach ($playerAnswers as $playerId => $answers) {
            $roundScore = 0;
            $uniqueCount = 0;
            $validCount = 0;
            $categoryDetails = [];

            foreach ($categories as $category) {
                $raw = trim($answers[$category] ?? '');
                $normalized = mb_strtolower($raw);
                $isValid = $validity[$playerId][$category] ?? false;
                $isUnique = $isValid && ($occurrences[$category][$normalized] ?? 0) === 1;

                $points = 0;
                if ($isValid) {
                    $validCount++;
                    $points = $isUnique ? self::UNIQUE_POINTS : self::SHARED_POINTS;
                    $roundScore += $points;
                    if ($isUnique) {
                        $uniqueCount++;
                    }
                }

                $categoryDetails[$category] = [
                    'value' => $raw,
                    'valid' => $isValid,
                    'unique' => $isUnique,
                    'points' => $points,
                ];
            }

            $hasPerfect = $validCount === count($categories);
            if ($hasPerfect) {
                $roundScore += self::PERFECT_BONUS;
            }

            $results[(string) $playerId] = [
                'round_score' => $roundScore,
                'unique_count' => $uniqueCount,
                'has_perfect' => $hasPerfect,
                'categories' => $categoryDetails,
            ];
        }

        return $results;
    }

    public function validateAnswer(string $answer, string $letter, string $category): bool
    {
        if (isset($this->validators[$category])) {
            return $this->validators[$category]->validate($answer, $letter);
        }

        // Fallback: just check it starts with the correct letter
        return mb_strtolower(mb_substr(trim($answer), 0, 1)) === mb_strtolower($letter);
    }

    /** @return array<string> */
    public static function drawAvailableLetters(): array
    {
        // Exclude uncommon letters in French: W, X, Y
        return str_split('ABCDEFGHIJKLMNOPRSTUVZ');
    }

    /**
     * @param  array<string>  $excludedLetters  Letters already used in the game
     */
    public static function drawLetter(array $excludedLetters = []): string
    {
        $available = array_values(array_diff(self::drawAvailableLetters(), $excludedLetters));

        // Fallback: if all letters have been used, reset to the full pool
        if (empty($available)) {
            $available = self::drawAvailableLetters();
        }

        return $available[array_rand($available)];
    }
}

<?php

namespace App\Domain\Game\DTO;

use App\Domain\Game\Enums\Category;

final readonly class SubmitAnswersDTO
{
    /**
     * @param  array<string, string>  $answers  {category: value}
     */
    public function __construct(
        public int $playerId,
        public int $roundId,
        public array $answers,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(int $playerId, int $roundId, array $data): self
    {
        $allowedCategories = Category::values();
        $answers = [];

        foreach ($allowedCategories as $category) {
            $answers[$category] = trim((string) ($data[$category] ?? ''));
        }

        return new self(
            playerId: $playerId,
            roundId: $roundId,
            answers: $answers,
        );
    }
}

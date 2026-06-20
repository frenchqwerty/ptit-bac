<?php

namespace App\Domain\Game\DTO;

final readonly class CreateGameRoomDTO
{
    /**
     * @param  array<string>|null  $categories
     */
    public function __construct(
        public int $hostId,
        public int $totalRounds = 5,
        public int $roundDuration = 90,
        public int $maxPlayers = 10,
        public ?array $categories = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(int $hostId, array $data): self
    {
        return new self(
            hostId: $hostId,
            totalRounds: (int) ($data['total_rounds'] ?? 5),
            roundDuration: (int) ($data['round_duration'] ?? 90),
            maxPlayers: (int) ($data['max_players'] ?? 10),
            categories: isset($data['categories']) ? (array) $data['categories'] : null,
        );
    }
}

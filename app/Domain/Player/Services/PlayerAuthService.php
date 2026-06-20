<?php

namespace App\Domain\Player\Services;

use App\Models\Player;

final class PlayerAuthService
{
    private const AVATAR_COLORS = [
        '#7C3AED', '#2563EB', '#0891B2', '#059669',
        '#D97706', '#DC2626', '#DB2777', '#7C3AED',
    ];

    public function loginOrCreate(string $name): array
    {
        $displayName = $this->sanitizeName($name);

        /** @var Player $player */
        $player = Player::firstOrCreate(
            ['name' => mb_strtolower($displayName)],
            [
                'display_name' => $displayName,
                'avatar_color' => $this->randomAvatarColor(),
                'elo_rating' => 1000,
                'best_elo' => 1000,
            ]
        );

        $token = $player->generateNewToken();

        $player->update([
            'is_online' => true,
            'last_seen_at' => now(),
        ]);

        // Ensure statistics row exists
        $player->getStatisticOrCreate();

        return [
            'player' => $player,
            'token' => $token,
        ];
    }

    public function logout(Player $player): void
    {
        $player->update([
            'session_token' => null,
            'is_online' => false,
            'last_seen_at' => now(),
        ]);
    }

    public function validateToken(string $token): ?Player
    {
        return Player::findByToken($token);
    }

    private function sanitizeName(string $name): string
    {
        $name = trim($name);
        $name = preg_replace('/[^a-zA-ZÀ-ÿ0-9\s\-_]/', '', $name) ?? $name;
        $name = trim($name);

        return mb_substr($name, 0, 50);
    }

    private function randomAvatarColor(): string
    {
        return self::AVATAR_COLORS[array_rand(self::AVATAR_COLORS)];
    }
}

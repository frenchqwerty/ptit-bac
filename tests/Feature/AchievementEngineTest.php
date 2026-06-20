<?php

use App\Domain\Achievement\Services\AchievementEngine;
use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('AchievementEngine', function (): void {
    beforeEach(function (): void {
        $this->engine = new AchievementEngine;
        AchievementEngine::seedDefaults();
    });

    it('awards first_win achievement when player wins their first game', function (): void {
        $player = Player::factory()->create();
        $stats = $player->statistic()->create(['games_won' => 1, 'games_played' => 1]);

        $player->load('statistic');
        $unlocked = $this->engine->checkAndAward($player, ['game_room_id' => null]);

        $keys = collect($unlocked)->pluck('key');
        expect($keys)->toContain('first_win');
    });

    it('does not award achievement twice', function (): void {
        $player = Player::factory()->create();
        $player->statistic()->create(['games_won' => 1, 'games_played' => 1]);
        $player->load('statistic');

        // First award
        $this->engine->checkAndAward($player, []);

        // Second attempt
        $player->refresh()->load(['statistic', 'achievements']);
        $unlocked = $this->engine->checkAndAward($player, []);

        expect($unlocked)->toBeEmpty();
    });

    it('awards speedster achievement when stop called under 10 seconds', function (): void {
        $player = Player::factory()->create();
        $player->statistic()->create(['games_played' => 1]);
        $player->load('statistic');

        $unlocked = $this->engine->checkAndAward($player, [
            'game_room_id' => null,
            'stopped_at_seconds' => 8,
        ]);

        $keys = collect($unlocked)->pluck('key');
        expect($keys)->toContain('speedster');
    });
});

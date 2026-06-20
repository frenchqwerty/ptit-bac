<?php

use App\Domain\Score\Services\EloService;
use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('EloService', function (): void {
    beforeEach(function (): void {
        $this->eloService = new EloService;
    });

    it('winner gains elo and loser loses elo in 1v1', function (): void {
        $winner = Player::factory()->make(['elo_rating' => 1000, 'id' => 1]);
        $loser = Player::factory()->make(['elo_rating' => 1000, 'id' => 2]);

        $results = $this->eloService->calculate([
            ['player' => $winner, 'position' => 1],
            ['player' => $loser, 'position' => 2],
        ]);

        $winnerResult = collect($results)->firstWhere('player.id', 1);
        $loserResult = collect($results)->firstWhere('player.id', 2);

        expect($winnerResult['elo_after'])->toBeGreaterThan(1000);
        expect($loserResult['elo_after'])->toBeLessThan(1000);
        expect($winnerResult['elo_change'] + $loserResult['elo_change'])->toBe(0);
    });

    it('returns correct structure', function (): void {
        $player = Player::factory()->make(['elo_rating' => 1200, 'id' => 1]);

        $results = $this->eloService->calculate([
            ['player' => $player, 'position' => 1],
        ]);

        expect($results[0])->toHaveKeys(['player', 'elo_before', 'elo_after', 'elo_change', 'position']);
    });

    it('elo does not go below minimum', function (): void {
        $weakPlayer = Player::factory()->make(['elo_rating' => 105, 'id' => 1]);
        $strongPlayers = [];
        for ($i = 2; $i <= 10; $i++) {
            $strongPlayers[] = ['player' => Player::factory()->make(['elo_rating' => 2000, 'id' => $i]), 'position' => $i - 1];
        }

        $results = $this->eloService->calculate(
            array_merge(
                [['player' => $weakPlayer, 'position' => 10]],
                $strongPlayers,
            )
        );

        $weakResult = collect($results)->firstWhere('player.id', 1);
        expect($weakResult['elo_after'])->toBeGreaterThanOrEqual(100);
    });
});

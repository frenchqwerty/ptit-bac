<?php

namespace App\Domain\Score\Services;

use App\Models\EloHistory;
use App\Models\Player;

/**
 * ELO rating system adapted for multiplayer (2–10 players).
 *
 * Uses a pairwise comparison approach:
 * Each player is compared to every other player based on finish position.
 * K-factor: 32 (standard chess K-factor).
 */
final class EloService
{
    private const K_FACTOR = 32;

    private const MIN_ELO = 100;

    private const MAX_ELO = 3000;

    /**
     * Calculate new ELO ratings for all players after a game.
     *
     * @param  array<array{player: Player, position: int}>  $results  Sorted by position (1 = winner)
     * @return array<array{player: Player, elo_before: int, elo_after: int, elo_change: int, position: int}>
     */
    public function calculate(array $results): array
    {
        $totalPlayers = count($results);
        $eloChanges = [];

        // Initialize changes for each player
        foreach ($results as $entry) {
            $eloChanges[$entry['player']->id] = 0;
        }

        // Pairwise comparison
        for ($i = 0; $i < $totalPlayers; $i++) {
            for ($j = $i + 1; $j < $totalPlayers; $j++) {
                $playerA = $results[$i];
                $playerB = $results[$j];

                // Player A has better position (lower = better)
                $actualScoreA = $playerA['position'] < $playerB['position'] ? 1.0 : ($playerA['position'] === $playerB['position'] ? 0.5 : 0.0);
                $actualScoreB = 1.0 - $actualScoreA;

                $expectedA = $this->expectedScore($playerA['player']->elo_rating, $playerB['player']->elo_rating);
                $expectedB = 1.0 - $expectedA;

                $eloChanges[$playerA['player']->id] += (int) round(self::K_FACTOR * ($actualScoreA - $expectedA));
                $eloChanges[$playerB['player']->id] += (int) round(self::K_FACTOR * ($actualScoreB - $expectedB));
            }
        }

        $output = [];

        foreach ($results as $entry) {
            $player = $entry['player'];
            $eloBefore = $player->elo_rating;
            $change = $eloChanges[$player->id];
            $eloAfter = max(self::MIN_ELO, min(self::MAX_ELO, $eloBefore + $change));

            $output[] = [
                'player' => $player,
                'elo_before' => $eloBefore,
                'elo_after' => $eloAfter,
                'elo_change' => $eloAfter - $eloBefore,
                'position' => $entry['position'],
            ];
        }

        return $output;
    }

    /**
     * Persist ELO changes to the database and update player records.
     *
     * @param  array<array{player: Player, elo_before: int, elo_after: int, elo_change: int, position: int}>  $eloResults
     */
    public function persist(array $eloResults, int $gameRoomId): void
    {
        $totalPlayers = count($eloResults);

        foreach ($eloResults as $result) {
            $player = $result['player'];

            // Update player ELO
            $player->update([
                'elo_rating' => $result['elo_after'],
                'best_elo' => max($player->best_elo, $result['elo_after']),
            ]);

            // Record history
            EloHistory::create([
                'player_id' => $player->id,
                'game_room_id' => $gameRoomId,
                'elo_before' => $result['elo_before'],
                'elo_after' => $result['elo_after'],
                'elo_change' => $result['elo_change'],
                'position' => $result['position'],
                'total_players' => $totalPlayers,
            ]);
        }
    }

    /**
     * Expected score based on ELO difference (logistic function).
     */
    private function expectedScore(int $ratingA, int $ratingB): float
    {
        return 1.0 / (1.0 + 10 ** (($ratingB - $ratingA) / 400));
    }
}

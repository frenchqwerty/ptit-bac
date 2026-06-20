<?php

namespace App\Domain\Game\Services;

use App\Domain\Achievement\Services\AchievementEngine;
use App\Domain\Game\Enums\GameStatus;
use App\Domain\Score\Services\EloService;
use App\Events\Game\AchievementUnlocked;
use App\Events\Game\GameFinished;
use App\Events\Game\LeaderboardUpdated;
use App\Models\GameHistory;
use App\Models\GameRoom;
use App\Models\Score;

final class GameFinishService
{
    public function __construct(
        private readonly EloService $eloService,
        private readonly AchievementEngine $achievementEngine,
    ) {}

    public function finish(GameRoom $room): GameHistory
    {
        $room->update(['status' => GameStatus::Finished]);

        // Build final standings
        $players = $room->players()->withPivot(['total_score', 'position'])->get();

        // Sort by total score descending
        $sorted = $players->sortByDesc(fn ($p) => $p->pivot->total_score)->values();

        // Assign positions
        $eloInput = [];
        $playersData = [];
        foreach ($sorted as $idx => $player) {
            $position = $idx + 1;

            $room->players()->updateExistingPivot($player->id, ['position' => $position]);

            $eloInput[] = [
                'player' => $player,
                'position' => $position,
            ];

            $playersData[] = [
                'id' => $player->id,
                'name' => $player->display_name,
                'score' => $player->pivot->total_score,
                'position' => $position,
            ];
        }

        $winner = $sorted->first();
        $highestScore = $sorted->first()?->pivot->total_score ?? 0;

        // Build rounds history
        $roundsData = $room->rounds()->with('scores', 'answers')->get()
            ->map(fn ($round) => [
                'number' => $round->round_number,
                'letter' => $round->letter,
                'duration' => $round->started_at?->diffInSeconds($round->stopped_at ?? $round->finished_at),
            ])->toArray();

        $lettersUsed = $room->rounds()->pluck('letter')->toArray();

        $startedAt = $room->rounds()->min('started_at');

        // Save game history
        $history = GameHistory::create([
            'game_room_id' => $room->id,
            'winner_id' => $winner?->id,
            'players_data' => $playersData,
            'rounds_data' => $roundsData,
            'letters_used' => $lettersUsed,
            'rounds_played' => $room->current_round,
            'highest_score' => $highestScore,
            'started_at' => $startedAt,
            'finished_at' => now(),
        ]);

        // Update ELO
        $eloResults = $this->eloService->calculate($eloInput);
        $this->eloService->persist($eloResults, $room->id);

        // Update statistics & check achievements
        foreach ($sorted as $idx => $player) {
            $isWinner = $idx === 0;
            $stats = $player->getStatisticOrCreate();
            $stats->incrementGamesPlayed($isWinner);
            $stats->increment('total_rounds_played', $room->current_round);

            if ($player->pivot->total_score > $stats->best_score) {
                $stats->update(['best_score' => $player->pivot->total_score]);
            }

            $stats->increment('total_score', $player->pivot->total_score);

            // Count unique answers
            $uniqueCount = Score::where('game_room_id', $room->id)
                ->where('player_id', $player->id)
                ->sum('unique_answers_count');

            $stats->increment('unique_answers_found', $uniqueCount);

            // Check for perfect rounds
            $perfectCount = Score::where('game_room_id', $room->id)
                ->where('player_id', $player->id)
                ->where('has_perfect', true)
                ->count();

            if ($perfectCount > 0) {
                $stats->increment('perfect_rounds', $perfectCount);
            }

            // Check achievements
            $stoppedAtSeconds = $room->rounds()
                ->whereNotNull('stopped_at')
                ->where('stopped_by_player_id', $player->id)
                ->get()
                ->map(fn ($r) => $r->started_at?->diffInSeconds($r->stopped_at))
                ->min();

            $newAchievements = $this->achievementEngine->checkAndAward($player, [
                'game_room_id' => $room->id,
                'stopped_at_seconds' => $stoppedAtSeconds,
                'is_winner' => $isWinner,
            ]);

            foreach ($newAchievements as $achievement) {
                broadcast(new AchievementUnlocked($room, $player, $achievement));
            }
        }

        broadcast(new GameFinished($room, $history));
        broadcast(new LeaderboardUpdated);

        return $history;
    }
}

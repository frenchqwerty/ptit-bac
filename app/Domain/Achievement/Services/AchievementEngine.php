<?php

namespace App\Domain\Achievement\Services;

use App\Models\Achievement;
use App\Models\Player;
use App\Models\Statistic;

/**
 * Checks and awards achievements after game-related events.
 *
 * Each achievement has a JSON criteria:
 *   {type: "games_played", threshold: 100}
 *   {type: "unique_answers", threshold: 10}
 *   {type: "win_streak", threshold: 5}
 *   {type: "fast_stop", max_seconds: 10}
 */
final class AchievementEngine
{
    /**
     * Check all achievements for a player after a game and award new ones.
     *
     * @param  array<string, mixed>  $gameContext  {game_room_id, stopped_at_seconds, is_winner}
     * @return array<Achievement> Newly unlocked achievements
     */
    public function checkAndAward(Player $player, array $gameContext = []): array
    {
        $unlockedAchievementIds = $player->achievements()->pluck('achievements.id')->toArray();
        $pendingAchievements = Achievement::where('is_active', true)
            ->whereNotIn('id', $unlockedAchievementIds)
            ->get();

        $stats = $player->statistic;
        $newlyUnlocked = [];

        foreach ($pendingAchievements as $achievement) {
            if ($this->meetsСriteria($achievement, $stats, $gameContext)) {
                $player->achievements()->attach($achievement->id, [
                    'game_room_id' => $gameContext['game_room_id'] ?? null,
                    'unlocked_at' => now(),
                ]);
                $newlyUnlocked[] = $achievement;
            }
        }

        return $newlyUnlocked;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function meetsСriteria(Achievement $achievement, ?Statistic $stats, array $context): bool
    {
        /** @var array<string, mixed> $criteria */
        $criteria = $achievement->criteria;
        $type = $criteria['type'] ?? '';

        return match ($type) {
            'games_played' => $stats !== null && $stats->games_played >= ($criteria['threshold'] ?? 0),
            'unique_answers' => $stats !== null && $stats->unique_answers_found >= ($criteria['threshold'] ?? 0),
            'win_streak' => $stats !== null && $stats->best_win_streak >= ($criteria['threshold'] ?? 0),
            'perfect_rounds' => $stats !== null && $stats->perfect_rounds >= ($criteria['threshold'] ?? 1),
            'fast_stop' => isset($context['stopped_at_seconds'])
                && $context['stopped_at_seconds'] <= ($criteria['max_seconds'] ?? 10),
            'games_won' => $stats !== null && $stats->games_won >= ($criteria['threshold'] ?? 1),
            default => false,
        };
    }

    /**
     * Seed default achievements into the database (idempotent).
     */
    public static function seedDefaults(): void
    {
        $defaults = [
            [
                'key' => 'veteran',
                'name' => 'Vétéran',
                'description' => '100 parties jouées',
                'icon' => 'shield',
                'category' => 'progression',
                'criteria' => ['type' => 'games_played', 'threshold' => 100],
                'reward_elo' => 50,
            ],
            [
                'key' => 'unique_master',
                'name' => 'Maître de l\'originalité',
                'description' => '10 réponses uniques trouvées',
                'icon' => 'star',
                'category' => 'skill',
                'criteria' => ['type' => 'unique_answers', 'threshold' => 10],
                'reward_elo' => 20,
            ],
            [
                'key' => 'speedster',
                'name' => 'Flash',
                'description' => 'Victoire avec moins de 10 secondes au chrono',
                'icon' => 'bolt',
                'category' => 'speed',
                'criteria' => ['type' => 'fast_stop', 'max_seconds' => 10],
                'reward_elo' => 30,
            ],
            [
                'key' => 'win_streak_5',
                'name' => 'Inarrêtable',
                'description' => 'Série de 5 victoires consécutives',
                'icon' => 'fire',
                'category' => 'domination',
                'criteria' => ['type' => 'win_streak', 'threshold' => 5],
                'reward_elo' => 75,
            ],
            [
                'key' => 'perfect_player',
                'name' => 'Perfectionniste',
                'description' => 'Obtenir un Perfect (toutes catégories valides)',
                'icon' => 'gem',
                'category' => 'skill',
                'criteria' => ['type' => 'perfect_rounds', 'threshold' => 1],
                'reward_elo' => 25,
            ],
            [
                'key' => 'first_win',
                'name' => 'Première victoire',
                'description' => 'Gagner sa première partie',
                'icon' => 'trophy',
                'category' => 'progression',
                'criteria' => ['type' => 'games_won', 'threshold' => 1],
                'reward_elo' => 10,
            ],
        ];

        foreach ($defaults as $data) {
            Achievement::firstOrCreate(
                ['key' => $data['key']],
                $data
            );
        }
    }
}

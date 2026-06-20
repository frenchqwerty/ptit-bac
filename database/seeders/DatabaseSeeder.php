<?php

namespace Database\Seeders;

use App\Domain\Achievement\Services\AchievementEngine;
use App\Domain\Game\Enums\Category;
use App\Domain\Game\Enums\GameStatus;
use App\Models\GameRoom;
use App\Models\Player;
use App\Models\Statistic;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed achievements
        AchievementEngine::seedDefaults();
        $this->command->info('✅ Achievements seeded');

        // Create demo players with stats
        $players = [];
        $names = ['Thomas', 'Lucas', 'Sarah', 'Emma', 'Noah', 'Léa', 'Hugo', 'Camille', 'Théo', 'Jade'];

        foreach ($names as $name) {
            /** @var Player $player */
            $player = Player::firstOrCreate(
                ['name' => mb_strtolower($name)],
                [
                    'uuid' => (string) Str::uuid(),
                    'display_name' => $name,
                    'avatar_color' => $this->randomColor(),
                    'elo_rating' => rand(900, 1400),
                    'best_elo' => rand(1100, 1800),
                    'is_online' => false,
                ]
            );

            Statistic::firstOrCreate(
                ['player_id' => $player->id],
                [
                    'games_played' => rand(10, 100),
                    'games_won' => rand(2, 40),
                    'best_score' => rand(50, 200),
                    'total_score' => rand(500, 5000),
                    'total_rounds_played' => rand(30, 300),
                    'unique_answers_found' => rand(5, 80),
                    'perfect_rounds' => rand(0, 20),
                    'current_win_streak' => rand(0, 5),
                    'best_win_streak' => rand(1, 10),
                    'total_answers_submitted' => rand(100, 1000),
                    'stop_rounds_called' => rand(0, 30),
                ]
            );

            $players[] = $player;
        }

        $this->command->info('✅ '.count($players).' players seeded');

        // Create a waiting demo room (no players attached)
        if (! GameRoom::where('code', 'DEMO01')->exists()) {
            GameRoom::create([
                'code' => 'DEMO01',
                'host_id' => $players[0]->id,
                'status' => GameStatus::Waiting,
                'total_rounds' => 5,
                'round_duration' => 90,
                'max_players' => 10,
                'categories' => Category::values(),
            ]);
            $this->command->info('✅ Demo room DEMO01 created');
        }
    }

    private function randomColor(): string
    {
        $colors = ['#7C3AED', '#2563EB', '#0891B2', '#059669', '#D97706', '#DC2626', '#DB2777'];

        return $colors[array_rand($colors)];
    }
}

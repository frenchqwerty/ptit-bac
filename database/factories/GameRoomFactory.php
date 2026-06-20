<?php

namespace Database\Factories;

use App\Domain\Game\Enums\Category;
use App\Domain\Game\Enums\GameStatus;
use App\Models\GameRoom;
use App\Models\Player;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<GameRoom>
 */
class GameRoomFactory extends Factory
{
    protected $model = GameRoom::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'code' => strtoupper(Str::random(6)),
            'host_id' => Player::factory(),
            'status' => GameStatus::Waiting,
            'total_rounds' => fake()->numberBetween(3, 8),
            'round_duration' => fake()->randomElement([60, 90, 120]),
            'max_players' => 10,
            'categories' => Category::values(),
        ];
    }

    public function waiting(): static
    {
        return $this->state(fn () => ['status' => GameStatus::Waiting]);
    }

    public function playing(): static
    {
        return $this->state(fn () => [
            'status' => GameStatus::Playing,
            'current_letter' => fake()->randomElement(str_split('ABCDEFGHIJKLMNOPRSTUVZ')),
            'current_round' => 1,
            'round_started_at' => now()->subSeconds(30),
            'round_ends_at' => now()->addSeconds(60),
        ]);
    }

    public function finished(): static
    {
        return $this->state(fn () => ['status' => GameStatus::Finished]);
    }
}

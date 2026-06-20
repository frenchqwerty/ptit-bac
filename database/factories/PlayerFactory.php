<?php

namespace Database\Factories;

use App\Models\Player;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Player>
 */
class PlayerFactory extends Factory
{
    protected $model = Player::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->firstName();
        $colors = ['#7C3AED', '#2563EB', '#0891B2', '#059669', '#D97706', '#DC2626', '#DB2777'];

        return [
            'uuid' => (string) Str::uuid(),
            'name' => mb_strtolower($name),
            'display_name' => $name,
            'avatar_color' => fake()->randomElement($colors),
            'session_token' => null,
            'elo_rating' => fake()->numberBetween(800, 1500),
            'best_elo' => fake()->numberBetween(800, 1800),
            'is_online' => fake()->boolean(30),
            'last_seen_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function online(): static
    {
        return $this->state(fn () => ['is_online' => true, 'last_seen_at' => now()]);
    }

    public function highRanked(): static
    {
        return $this->state(fn () => [
            'elo_rating' => fake()->numberBetween(1500, 2500),
            'best_elo' => fake()->numberBetween(1600, 2800),
        ]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $game_room_id
 * @property int|null $winner_id
 * @property array<mixed> $players_data
 * @property array<mixed> $rounds_data
 * @property array<string> $letters_used
 * @property int $rounds_played
 * @property int $highest_score
 * @property Carbon|null $started_at
 * @property Carbon|null $finished_at
 */
#[Fillable(['game_room_id', 'winner_id', 'players_data', 'rounds_data', 'letters_used', 'rounds_played', 'highest_score', 'started_at', 'finished_at'])]
class GameHistory extends Model
{
    protected $casts = [
        'players_data' => 'array',
        'rounds_data' => 'array',
        'letters_used' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function gameRoom(): BelongsTo
    {
        return $this->belongsTo(GameRoom::class);
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'winner_id');
    }

    public function getDurationInMinutes(): float
    {
        if (! $this->started_at || ! $this->finished_at) {
            return 0;
        }

        return round($this->started_at->diffInSeconds($this->finished_at) / 60, 1);
    }
}

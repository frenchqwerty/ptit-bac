<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $round_id
 * @property int $player_id
 * @property int $game_room_id
 * @property int $round_score
 * @property int $cumulative_score
 * @property bool $has_perfect
 * @property int $unique_answers_count
 */
#[Fillable(['round_id', 'player_id', 'game_room_id', 'round_score', 'cumulative_score', 'has_perfect', 'unique_answers_count'])]
class Score extends Model
{
    protected $casts = [
        'has_perfect' => 'boolean',
    ];

    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function gameRoom(): BelongsTo
    {
        return $this->belongsTo(GameRoom::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $player_id
 * @property int $achievement_id
 * @property int|null $game_room_id
 * @property Carbon $unlocked_at
 */
#[Fillable(['player_id', 'achievement_id', 'game_room_id', 'unlocked_at'])]
class PlayerAchievement extends Model
{
    protected $casts = [
        'unlocked_at' => 'datetime',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class);
    }

    public function gameRoom(): BelongsTo
    {
        return $this->belongsTo(GameRoom::class);
    }
}

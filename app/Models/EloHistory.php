<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $player_id
 * @property int|null $game_room_id
 * @property int $elo_before
 * @property int $elo_after
 * @property int $elo_change
 * @property int|null $position
 * @property int|null $total_players
 */
#[Fillable(['player_id', 'game_room_id', 'elo_before', 'elo_after', 'elo_change', 'position', 'total_players'])]
class EloHistory extends Model
{
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function gameRoom(): BelongsTo
    {
        return $this->belongsTo(GameRoom::class);
    }

    public function isGain(): bool
    {
        return $this->elo_change > 0;
    }
}

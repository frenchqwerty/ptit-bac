<?php

namespace App\Models;

use App\Domain\Game\Enums\RoundStatus;
use Database\Factories\RoundFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $game_room_id
 * @property int $round_number
 * @property string $letter
 * @property RoundStatus $status
 * @property int|null $stopped_by_player_id
 * @property Carbon|null $started_at
 * @property Carbon|null $stopped_at
 * @property Carbon|null $finished_at
 */
#[Fillable(['game_room_id', 'round_number', 'letter', 'status', 'stopped_by_player_id', 'started_at', 'stopped_at', 'finished_at'])]
#[UseFactory(RoundFactory::class)]
class Round extends Model
{
    use HasFactory;

    protected $casts = [
        'status' => RoundStatus::class,
        'started_at' => 'datetime',
        'stopped_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function gameRoom(): BelongsTo
    {
        return $this->belongsTo(GameRoom::class);
    }

    public function stoppedBy(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'stopped_by_player_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    /** @return array<string, array<Answer>> */
    public function getAnswersByCategory(): array
    {
        return $this->answers->groupBy('category')->toArray();
    }
}

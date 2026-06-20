<?php

namespace App\Models;

use App\Domain\Game\Enums\Category;
use App\Domain\Game\Enums\GameStatus;
use Database\Factories\GameRoomFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $code
 * @property int $host_id
 * @property GameStatus $status
 * @property string|null $current_letter
 * @property int $current_round
 * @property int $total_rounds
 * @property int $round_duration
 * @property int $max_players
 * @property array<string> $categories
 * @property Carbon|null $round_started_at
 * @property Carbon|null $round_ends_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['code', 'host_id', 'status', 'current_letter', 'current_round', 'total_rounds', 'round_duration', 'max_players', 'categories', 'round_started_at', 'round_ends_at'])]
#[UseFactory(GameRoomFactory::class)]
class GameRoom extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'status' => GameStatus::class,
        'categories' => 'array',
        'round_started_at' => 'datetime',
        'round_ends_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (GameRoom $room): void {
            if (empty($room->code)) {
                $room->code = self::generateUniqueCode();
            }
            if (empty($room->categories)) {
                $room->categories = Category::values();
            }
        });
    }

    private static function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'host_id');
    }

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'game_room_players')
            ->withPivot(['is_ready', 'is_connected', 'total_score', 'position', 'joined_at'])
            ->withTimestamps();
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(Round::class);
    }

    public function currentRound(): HasMany
    {
        return $this->hasMany(Round::class)->where('round_number', $this->current_round);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    public function gameHistory(): HasMany
    {
        return $this->hasMany(GameHistory::class);
    }

    public function isWaiting(): bool
    {
        return $this->status === GameStatus::Waiting;
    }

    public function isPlaying(): bool
    {
        return $this->status === GameStatus::Playing;
    }

    public function isFull(): bool
    {
        return $this->players()->count() >= $this->max_players;
    }

    public function hasPlayer(Player $player): bool
    {
        return $this->players()->where('players.id', $player->id)->exists();
    }

    public function getRemainingSeconds(): int
    {
        if (! $this->round_ends_at) {
            return 0;
        }

        return max(0, now()->diffInSeconds($this->round_ends_at, false));
    }
}

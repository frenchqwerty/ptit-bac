<?php

namespace App\Models;

use App\Domain\Game\Enums\Category;
use Database\Factories\PlayerFactory;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $display_name
 * @property string $avatar_color
 * @property string|null $session_token
 * @property int $elo_rating
 * @property int $best_elo
 * @property bool $is_online
 * @property Carbon|null $last_seen_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'display_name', 'avatar_color', 'session_token', 'elo_rating', 'best_elo', 'is_online', 'last_seen_at'])]
#[UseFactory(PlayerFactory::class)]
class Player extends Model implements AuthenticatableContract
{
    use Authenticatable, HasFactory;

    protected $casts = [
        'is_online' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Player $player): void {
            if (empty($player->uuid)) {
                $player->uuid = (string) Str::uuid();
            }
        });
    }

    public function gameRooms(): BelongsToMany
    {
        return $this->belongsToMany(GameRoom::class, 'game_room_players')
            ->withPivot(['is_ready', 'is_connected', 'total_score', 'position', 'joined_at'])
            ->withTimestamps();
    }

    public function hostedRooms(): HasMany
    {
        return $this->hasMany(GameRoom::class, 'host_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    public function achievements(): BelongsToMany
    {
        return $this->belongsToMany(Achievement::class, 'player_achievements')
            ->withPivot(['game_room_id', 'unlocked_at'])
            ->withTimestamps();
    }

    public function eloHistories(): HasMany
    {
        return $this->hasMany(EloHistory::class);
    }

    public function statistic(): HasOne
    {
        return $this->hasOne(Statistic::class);
    }

    public function gameHistoriesWon(): HasMany
    {
        return $this->hasMany(GameHistory::class, 'winner_id');
    }

    public function getStatisticOrCreate(): Statistic
    {
        return $this->statistic ?? $this->statistic()->create();
    }

    public function getWinRateAttribute(): float
    {
        $stats = $this->statistic;
        if (! $stats || $stats->games_played === 0) {
            return 0.0;
        }

        return round(($stats->games_won / $stats->games_played) * 100, 1);
    }

    public function getAverageScoreAttribute(): float
    {
        $stats = $this->statistic;
        if (! $stats || $stats->games_played === 0) {
            return 0.0;
        }

        return round($stats->total_score / $stats->games_played, 1);
    }

    public static function findByToken(string $token): ?self
    {
        return self::where('session_token', $token)->first();
    }

    public static function findByUuid(string $uuid): ?self
    {
        return self::where('uuid', $uuid)->first();
    }

    public function generateNewToken(): string
    {
        $token = Str::random(64);
        $this->update(['session_token' => $token]);

        return $token;
    }

    /** @return array<string> */
    public function getDefaultCategories(): array
    {
        return Category::values();
    }
}

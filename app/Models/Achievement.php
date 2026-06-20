<?php

namespace App\Models;

use Database\Factories\AchievementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $key
 * @property string $name
 * @property string $description
 * @property string $icon
 * @property string $category
 * @property array<mixed> $criteria
 * @property int $reward_elo
 * @property bool $is_active
 */
#[Fillable(['key', 'name', 'description', 'icon', 'category', 'criteria', 'reward_elo', 'is_active'])]
#[UseFactory(AchievementFactory::class)]
class Achievement extends Model
{
    use HasFactory;

    protected $casts = [
        'criteria' => 'array',
        'is_active' => 'boolean',
    ];

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'player_achievements')
            ->withPivot(['game_room_id', 'unlocked_at'])
            ->withTimestamps();
    }

    public static function findByKey(string $key): ?self
    {
        return self::where('key', $key)->where('is_active', true)->first();
    }
}

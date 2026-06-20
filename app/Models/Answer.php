<?php

namespace App\Models;

use App\Domain\Game\Enums\Category;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $round_id
 * @property int $player_id
 * @property string $category
 * @property string|null $value
 * @property bool|null $is_valid
 * @property bool|null $is_unique
 * @property int $points
 * @property Carbon|null $submitted_at
 */
#[Fillable(['round_id', 'player_id', 'category', 'value', 'is_valid', 'is_unique', 'points', 'submitted_at'])]
class Answer extends Model
{
    protected $casts = [
        'is_valid' => 'boolean',
        'is_unique' => 'boolean',
        'submitted_at' => 'datetime',
    ];

    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function getCategoryEnum(): Category
    {
        return Category::from($this->category);
    }

    public function isEmpty(): bool
    {
        return empty(trim((string) $this->value));
    }
}

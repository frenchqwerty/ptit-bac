<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vote extends Model
{
    protected $fillable = [
        'round_id',
        'voter_id',
        'answer_player_id',
        'category',
        'is_valid',
    ];

    protected $casts = [
        'is_valid' => 'boolean',
    ];

    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    public function voter(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'voter_id');
    }

    public function answerPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'answer_player_id');
    }
}

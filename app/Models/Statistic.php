<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $player_id
 * @property int $games_played
 * @property int $games_won
 * @property int $best_score
 * @property int $total_score
 * @property int $total_rounds_played
 * @property int $unique_answers_found
 * @property int $perfect_rounds
 * @property int $current_win_streak
 * @property int $best_win_streak
 * @property int $total_answers_submitted
 * @property int $stop_rounds_called
 */
#[Fillable([
    'player_id', 'games_played', 'games_won', 'best_score', 'total_score',
    'total_rounds_played', 'unique_answers_found', 'perfect_rounds',
    'current_win_streak', 'best_win_streak', 'total_answers_submitted', 'stop_rounds_called',
])]
class Statistic extends Model
{
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function getAverageScorePerGame(): float
    {
        if ($this->games_played === 0) {
            return 0.0;
        }

        return round($this->total_score / $this->games_played, 1);
    }

    public function getWinRate(): float
    {
        if ($this->games_played === 0) {
            return 0.0;
        }

        return round(($this->games_won / $this->games_played) * 100, 1);
    }

    public function incrementGamesPlayed(bool $isWinner): void
    {
        $this->increment('games_played');
        if ($isWinner) {
            $this->increment('games_won');
            $this->increment('current_win_streak');
            if ($this->current_win_streak > $this->best_win_streak) {
                $this->update(['best_win_streak' => $this->current_win_streak]);
            }
        } else {
            $this->update(['current_win_streak' => 0]);
        }
    }
}

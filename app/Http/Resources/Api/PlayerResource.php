<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'display_name' => $this->display_name,
            'avatar_color' => $this->avatar_color,
            'elo_rating' => $this->elo_rating,
            'best_elo' => $this->best_elo,
            'is_online' => $this->is_online,
            'win_rate' => $this->win_rate,
            'average_score' => $this->average_score,
            'statistic' => $this->whenLoaded('statistic'),
            'achievements_count' => $this->whenLoaded(
                'achievements',
                fn () => $this->achievements->count()
            ),
            'joined_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

<?php

namespace App\Http\Resources\Api;

use App\Domain\Game\Enums\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameRoomResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'current_letter' => $this->current_letter,
            'current_round' => $this->current_round,
            'total_rounds' => $this->total_rounds,
            'round_duration' => $this->round_duration,
            'max_players' => $this->max_players,
            'players_count' => $this->players_count ?? $this->players->count(),
            'categories' => $this->categories,
            'categories_labels' => Category::labelsMap(),
            'round_ends_at' => $this->round_ends_at?->toIso8601String(),
            'remaining_seconds' => $this->getRemainingSeconds(),
            'host' => new PlayerResource($this->whenLoaded('host')),
            'players' => PlayerResource::collection($this->whenLoaded('players')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

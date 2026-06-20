<?php

namespace App\Events\Game;

use App\Models\GameRoom;
use App\Models\Round;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScoresCalculated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<string, array{round_score: int, unique_count: int, has_perfect: bool, categories: array<string, array{value: string, valid: bool, unique: bool, points: int}>}>  $scoreResults
     */
    public function __construct(
        public readonly GameRoom $room,
        public readonly Round $round,
        public readonly array $scoreResults,
    ) {}

    public function broadcastOn(): Channel
    {
        return new PresenceChannel("game.{$this->room->code}");
    }

    public function broadcastAs(): string
    {
        return 'scores.calculated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'round_number' => $this->round->round_number,
            'letter' => $this->round->letter,
            'scores' => $this->scoreResults,
        ];
    }
}

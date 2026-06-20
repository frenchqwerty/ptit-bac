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

class LetterGenerated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly GameRoom $room,
        public readonly Round $round,
    ) {}

    public function broadcastOn(): Channel
    {
        return new PresenceChannel("game.{$this->room->code}");
    }

    public function broadcastAs(): string
    {
        return 'letter.generated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'letter' => $this->round->letter,
            'round_number' => $this->round->round_number,
            'total_rounds' => $this->room->total_rounds,
            'round_ends_at' => $this->room->round_ends_at?->toIso8601String(),
            'duration_seconds' => $this->room->round_duration,
        ];
    }
}

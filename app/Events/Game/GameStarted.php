<?php

namespace App\Events\Game;

use App\Models\GameRoom;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly GameRoom $room,
    ) {}

    public function broadcastOn(): Channel
    {
        return new PresenceChannel("game.{$this->room->code}");
    }

    public function broadcastAs(): string
    {
        return 'game.started';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'room_code' => $this->room->code,
            'total_rounds' => $this->room->total_rounds,
            'round_duration' => $this->room->round_duration,
            'categories' => $this->room->categories,
        ];
    }
}

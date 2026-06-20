<?php

namespace App\Events\Game;

use App\Models\GameRoom;
use App\Models\Player;
use App\Models\Round;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerStoppedRound implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly GameRoom $room,
        public readonly Player $player,
        public readonly Round $round,
    ) {}

    public function broadcastOn(): Channel
    {
        return new PresenceChannel("game.{$this->room->code}");
    }

    public function broadcastAs(): string
    {
        return 'round.stopped';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'player_name' => $this->player->display_name,
            'round_number' => $this->round->round_number,
            'stopped_at' => $this->round->stopped_at?->toIso8601String(),
        ];
    }
}

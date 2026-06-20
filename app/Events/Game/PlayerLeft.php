<?php

namespace App\Events\Game;

use App\Models\GameRoom;
use App\Models\Player;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerLeft implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly GameRoom $room,
        public readonly Player $player,
    ) {}

    public function broadcastOn(): Channel
    {
        return new PresenceChannel("game.{$this->room->code}");
    }

    public function broadcastAs(): string
    {
        return 'player.left';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'player_id' => $this->player->id,
            'player_uuid' => $this->player->uuid,
            'display_name' => $this->player->display_name,
            'players_count' => $this->room->players()->count(),
        ];
    }
}

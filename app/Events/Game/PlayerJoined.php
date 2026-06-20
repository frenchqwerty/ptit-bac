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

class PlayerJoined implements ShouldBroadcast
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
        return 'player.joined';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'player' => [
                'id' => $this->player->id,
                'uuid' => $this->player->uuid,
                'display_name' => $this->player->display_name,
                'avatar_color' => $this->player->avatar_color,
                'elo_rating' => $this->player->elo_rating,
            ],
            'players_count' => $this->room->players()->count(),
        ];
    }
}

<?php

namespace App\Events\Game;

use App\Models\Achievement;
use App\Models\GameRoom;
use App\Models\Player;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AchievementUnlocked implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly GameRoom $room,
        public readonly Player $player,
        public readonly Achievement $achievement,
    ) {}

    public function broadcastOn(): Channel
    {
        return new PresenceChannel("game.{$this->room->code}");
    }

    public function broadcastAs(): string
    {
        return 'achievement.unlocked';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'player_id' => $this->player->id,
            'player_name' => $this->player->display_name,
            'achievement' => [
                'key' => $this->achievement->key,
                'name' => $this->achievement->name,
                'description' => $this->achievement->description,
                'icon' => $this->achievement->icon,
            ],
        ];
    }
}

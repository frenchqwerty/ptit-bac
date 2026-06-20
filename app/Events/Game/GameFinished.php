<?php

namespace App\Events\Game;

use App\Models\GameHistory;
use App\Models\GameRoom;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameFinished implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly GameRoom $room,
        public readonly GameHistory $history,
    ) {}

    public function broadcastOn(): Channel
    {
        return new PresenceChannel("game.{$this->room->code}");
    }

    public function broadcastAs(): string
    {
        return 'game.finished';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'winner' => $this->history->players_data[0] ?? null,
            'players' => $this->history->players_data,
            'highest_score' => $this->history->highest_score,
            'rounds_played' => $this->history->rounds_played,
        ];
    }
}

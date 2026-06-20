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

class VoteSubmitted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly GameRoom $room,
        public readonly Player $voter,
        public readonly int $votedCount,
        public readonly int $totalCount,
    ) {}

    public function broadcastOn(): Channel
    {
        return new PresenceChannel("game.{$this->room->code}");
    }

    public function broadcastAs(): string
    {
        return 'vote.submitted';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'voter_id' => $this->voter->id,
            'voter_name' => $this->voter->display_name,
            'voted_count' => $this->votedCount,
            'total_count' => $this->totalCount,
        ];
    }
}

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

class VotingStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<int, array<string, mixed>>  $playersAnswers
     */
    public function __construct(
        public readonly GameRoom $room,
        public readonly Round $round,
        public readonly array $playersAnswers,
    ) {}

    public function broadcastOn(): Channel
    {
        return new PresenceChannel("game.{$this->room->code}");
    }

    public function broadcastAs(): string
    {
        return 'voting.started';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'round_number' => $this->round->round_number,
            'letter' => $this->round->letter,
            'players_answers' => $this->playersAnswers,
        ];
    }
}

<?php

namespace App\Domain\Game\Services;

use App\Domain\Game\DTO\CreateGameRoomDTO;
use App\Domain\Game\Enums\GameStatus;
use App\Events\Game\GameStarted;
use App\Events\Game\PlayerJoined;
use App\Events\Game\PlayerLeft;
use App\Exceptions\GameRoomFullException;
use App\Models\GameRoom;
use App\Models\Player;

final class GameRoomService
{
    public function create(CreateGameRoomDTO $dto): GameRoom
    {
        $room = GameRoom::create([
            'host_id' => $dto->hostId,
            'total_rounds' => $dto->totalRounds,
            'round_duration' => $dto->roundDuration,
            'max_players' => $dto->maxPlayers,
            'categories' => $dto->categories,
            'status' => GameStatus::Waiting,
        ]);

        // Host automatically joins their room
        $room->players()->attach($dto->hostId, [
            'is_ready' => false,
            'is_connected' => true,
            'total_score' => 0,
            'joined_at' => now(),
        ]);

        return $room->load(['host', 'players']);
    }

    public function join(string $roomCode, Player $player): GameRoom
    {
        $room = GameRoom::where('code', $roomCode)
            ->where('status', GameStatus::Waiting)
            ->firstOrFail();

        if ($room->hasPlayer($player)) {
            return $room->load(['host', 'players']);
        }

        if ($room->isFull()) {
            throw new GameRoomFullException('Room is full.');
        }

        $room->players()->attach($player->id, [
            'is_ready' => false,
            'is_connected' => true,
            'total_score' => 0,
            'joined_at' => now(),
        ]);

        $room->load(['host', 'players']);

        broadcast(new PlayerJoined($room, $player))->toOthers();

        return $room;
    }

    public function leave(GameRoom $room, Player $player): void
    {
        $room->players()->detach($player->id);

        broadcast(new PlayerLeft($room, $player))->toOthers();

        // If host leaves and room is still waiting, assign new host or close room
        if ($room->host_id === $player->id) {
            $nextPlayer = $room->players()->first();
            if ($nextPlayer) {
                $room->update(['host_id' => $nextPlayer->id]);
            } else {
                $room->update(['status' => GameStatus::Finished]);
            }
        }
    }

    public function startCountdown(GameRoom $room, Player $host): void
    {
        if ($room->host_id !== $host->id) {
            abort(403, 'Only the host can start the game.');
        }

        if ($room->players()->count() < 2) {
            abort(422, 'Need at least 2 players to start.');
        }

        $room->update(['status' => GameStatus::Countdown]);

        broadcast(new GameStarted($room));
    }

    public function setPlayerReady(GameRoom $room, Player $player, bool $isReady): void
    {
        $room->players()->updateExistingPivot($player->id, ['is_ready' => $isReady]);
    }
}

<?php

use App\Models\GameRoom;
use App\Models\Player;
use Illuminate\Support\Facades\Broadcast;

// Game room presence channel — players must be in the room
Broadcast::channel('game.{code}', function (mixed $user, string $code): array|false {
    // In our case, $user is actually a Player identified by token
    if (! $user instanceof Player) {
        return false;
    }

    $room = GameRoom::where('code', $code)->first();

    if (! $room) {
        return false;
    }

    // Allow access if player is in the room or room is still waiting (they can join)
    if (! $room->hasPlayer($user) && ! $room->isWaiting()) {
        return false;
    }

    return [
        'id' => $user->id,
        'uuid' => $user->uuid,
        'display_name' => $user->display_name,
        'avatar_color' => $user->avatar_color,
    ];
});

// Public leaderboard channel
Broadcast::channel('leaderboard', fn (): bool => true);

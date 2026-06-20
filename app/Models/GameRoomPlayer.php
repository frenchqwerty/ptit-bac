<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameRoomPlayer extends Model
{
    protected $table = 'game_room_players';

    protected $casts = [
        'is_ready' => 'boolean',
        'is_connected' => 'boolean',
        'joined_at' => 'datetime',
    ];
}

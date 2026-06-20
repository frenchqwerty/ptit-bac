<?php

namespace App\Http\Controllers\Api;

use App\Domain\Player\Services\PlayerAuthService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Resources\Api\GameRoomResource;
use App\Http\Resources\Api\PlayerResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlayerAuthController extends Controller
{
    public function __construct(
        private readonly PlayerAuthService $authService,
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->loginOrCreate($request->validated('name'));

        return response()->json([
            'player' => new PlayerResource($result['player']),
            'token' => $result['token'],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $player = $request->attributes->get('player');

        if (! $player) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $currentRoom = $player->gameRooms()
            ->whereIn('status', ['waiting', 'countdown', 'playing', 'scoring'])
            ->with(['host', 'players'])
            ->latest('game_room_players.joined_at')
            ->first();

        return response()->json([
            'player' => new PlayerResource($player->load(['statistic', 'achievements'])),
            'current_room' => $currentRoom ? new GameRoomResource($currentRoom) : null,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $player = $request->attributes->get('player');

        if ($player) {
            $this->authService->logout($player);
        }

        return response()->json(['message' => 'Logged out']);
    }
}

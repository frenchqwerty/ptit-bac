<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\PlayerResource;
use App\Models\Player;
use Illuminate\Http\JsonResponse;

class LeaderboardController extends Controller
{
    public function index(): JsonResponse
    {
        $players = Player::with('statistic')
            ->orderByDesc('elo_rating')
            ->limit(100)
            ->get();

        return response()->json([
            'leaderboard' => PlayerResource::collection($players),
        ]);
    }
}

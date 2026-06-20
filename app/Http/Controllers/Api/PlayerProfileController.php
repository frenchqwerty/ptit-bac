<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\PlayerResource;
use App\Models\GameHistory;
use App\Models\Player;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlayerProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        /** @var Player $player */
        $player = $request->attributes->get('player');
        $player->load(['statistic', 'achievements', 'eloHistories' => fn ($q) => $q->latest()->limit(20)]);

        return response()->json([
            'player' => new PlayerResource($player),
            'statistics' => $player->statistic,
            'achievements' => $player->achievements,
            'elo_history' => $player->eloHistories,
        ]);
    }

    public function gameHistory(Request $request): JsonResponse
    {
        /** @var Player $player */
        $player = $request->attributes->get('player');

        $history = GameHistory::whereJsonContains('players_data', [['id' => $player->id]])
            ->with('winner')
            ->latest('finished_at')
            ->paginate(20);

        return response()->json($history);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Domain\Game\DTO\CreateGameRoomDTO;
use App\Domain\Game\Services\GameFinishService;
use App\Domain\Game\Services\GameRoomService;
use App\Domain\Game\Services\RoundService;
use App\Exceptions\GameRoomFullException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateGameRoomRequest;
use App\Http\Resources\Api\GameRoomResource;
use App\Models\GameRoom;
use App\Models\Player;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameRoomController extends Controller
{
    public function __construct(
        private readonly GameRoomService $gameRoomService,
        private readonly RoundService $roundService,
        private readonly GameFinishService $gameFinishService,
    ) {}

    public function index(): JsonResponse
    {
        $rooms = GameRoom::where('status', 'waiting')
            ->with(['host', 'players'])
            ->withCount('players')
            ->latest()
            ->get();

        return response()->json([
            'rooms' => GameRoomResource::collection($rooms),
        ]);
    }

    public function store(CreateGameRoomRequest $request): JsonResponse
    {
        /** @var Player $player */
        $player = $request->attributes->get('player');

        $dto = CreateGameRoomDTO::fromArray($player->id, $request->validated());
        $room = $this->gameRoomService->create($dto);

        return response()->json([
            'room' => new GameRoomResource($room),
        ], 201);
    }

    public function show(string $code): JsonResponse
    {
        $room = GameRoom::where('code', $code)
            ->with(['host', 'players', 'rounds'])
            ->firstOrFail();

        return response()->json([
            'room' => new GameRoomResource($room),
        ]);
    }

    public function join(Request $request, string $code): JsonResponse
    {
        /** @var Player $player */
        $player = $request->attributes->get('player');

        try {
            $room = $this->gameRoomService->join($code, $player);

            return response()->json([
                'room' => new GameRoomResource($room),
            ]);
        } catch (GameRoomFullException $e) {
            return response()->json(['message' => 'Room is full'], 422);
        }
    }

    public function leave(Request $request, string $code): JsonResponse
    {
        /** @var Player $player */
        $player = $request->attributes->get('player');
        $room = GameRoom::where('code', $code)->firstOrFail();

        $this->gameRoomService->leave($room, $player);

        return response()->json(['message' => 'Left room']);
    }

    public function start(Request $request, string $code): JsonResponse
    {
        /** @var Player $player */
        $player = $request->attributes->get('player');
        $room = GameRoom::where('code', $code)->firstOrFail();

        $this->gameRoomService->startCountdown($room, $player);

        // Start first round
        $round = $this->roundService->startRound($room->fresh());

        return response()->json([
            'message' => 'Game started',
            'letter' => $round->letter,
            'round_number' => $round->round_number,
        ]);
    }
}

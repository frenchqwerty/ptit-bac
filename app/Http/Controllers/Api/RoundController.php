<?php

namespace App\Http\Controllers\Api;

use App\Domain\Game\DTO\SubmitAnswersDTO;
use App\Domain\Game\Services\RoundService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SubmitAnswersRequest;
use App\Models\GameRoom;
use App\Models\Player;
use App\Models\Round;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoundController extends Controller
{
    public function __construct(
        private readonly RoundService $roundService,
    ) {}

    public function submit(SubmitAnswersRequest $request, string $code): JsonResponse
    {
        /** @var Player $player */
        $player = $request->attributes->get('player');
        $room = GameRoom::where('code', $code)->firstOrFail();
        $round = Round::where('game_room_id', $room->id)
            ->where('round_number', $room->current_round)
            ->firstOrFail();

        $dto = SubmitAnswersDTO::fromArray($player->id, $round->id, $request->validated('answers', []));
        $this->roundService->submitAnswers($dto);

        // Auto-stop when every player has submitted their answers
        if ($this->roundService->allPlayersSubmitted($room, $round)) {
            $this->executeStop($room, $player);

            return response()->json(['message' => 'Answers submitted, round stopped']);
        }

        return response()->json(['message' => 'Answers submitted']);
    }

    public function stop(Request $request, string $code): JsonResponse
    {
        /** @var Player $player */
        $player = $request->attributes->get('player');
        $room = GameRoom::where('code', $code)->firstOrFail();

        $this->executeStop($room, $player);

        return response()->json(['message' => 'Round stopped']);
    }

    private function executeStop(GameRoom $room, Player $player): void
    {
        $this->roundService->stopRound($room, $player);
        // Voting phase starts inside stopRound() — VoteController handles scoring + next round
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Domain\Game\Enums\RoundStatus;
use App\Domain\Game\Services\GameFinishService;
use App\Domain\Game\Services\RoundService;
use App\Domain\Game\Services\VotingService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SubmitVotesRequest;
use App\Models\GameRoom;
use App\Models\Player;
use App\Models\Round;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    public function __construct(
        private readonly VotingService $votingService,
        private readonly RoundService $roundService,
        private readonly GameFinishService $gameFinishService,
    ) {}

    public function submit(SubmitVotesRequest $request, string $code): JsonResponse
    {
        /** @var Player $player */
        $player = $request->attributes->get('player');
        $room = GameRoom::where('code', $code)->firstOrFail();
        $round = $this->currentRound($room);

        if ($round->status !== RoundStatus::Scoring) {
            return response()->json(['message' => 'Voting phase is no longer active'], 422);
        }

        if ($this->votingService->hasVoted($round, $player)) {
            return response()->json(['message' => 'Already voted'], 422);
        }

        /** @var array<string, array<string, bool>> $votes */
        $votes = $request->validated('votes', []);

        $allVoted = $this->votingService->submitVotes($room, $round, $player, $votes);

        if ($allVoted) {
            $this->executeScoring($room, $round);

            return response()->json(['message' => 'Voting complete, scores calculated']);
        }

        return response()->json(['message' => 'Vote submitted']);
    }

    public function forceClose(Request $request, string $code): JsonResponse
    {
        /** @var Player $player */
        $player = $request->attributes->get('player');
        $room = GameRoom::where('code', $code)->firstOrFail();

        if ($room->host_player_id !== $player->id) {
            return response()->json(['message' => 'Only the host can close voting'], 403);
        }

        $round = $this->currentRound($room);

        if ($round->status !== RoundStatus::Scoring) {
            return response()->json(['message' => 'Voting phase is no longer active'], 422);
        }

        $this->votingService->fillMissingVotes($room, $round);
        $this->executeScoring($room, $round);

        return response()->json(['message' => 'Voting closed, scores calculated']);
    }

    private function executeScoring(GameRoom $room, Round $round): void
    {
        // Atomic guard: same UPDATE-WHERE pattern as RoundService::stopRound().
        // Prevents concurrent requests (e.g. two simultaneous last votes) from
        // running the scoring pipeline twice.
        $claimed = Round::where('id', $round->id)
            ->where('status', RoundStatus::Scoring)
            ->update(['status' => RoundStatus::Finished, 'finished_at' => now()]);

        if ($claimed === 0) {
            return; // Another concurrent request already processed voting
        }

        $manualValidity = $this->votingService->buildManualValidity($room, $round);
        $this->roundService->processScoring($room, $round, $manualValidity);

        $room->refresh();
        if ($room->current_round >= $room->total_rounds) {
            $this->gameFinishService->finish($room);

            return;
        }

        $this->roundService->startRound($room);
    }

    private function currentRound(GameRoom $room): Round
    {
        return Round::where('game_room_id', $room->id)
            ->where('round_number', $room->current_round)
            ->firstOrFail();
    }
}

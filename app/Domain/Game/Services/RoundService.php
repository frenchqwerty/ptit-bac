<?php

namespace App\Domain\Game\Services;

use App\Domain\Game\DTO\SubmitAnswersDTO;
use App\Domain\Game\Enums\GameStatus;
use App\Domain\Game\Enums\RoundStatus;
use App\Domain\Score\Services\ScoreEngine;
use App\Events\Game\LetterGenerated;
use App\Events\Game\PlayerStoppedRound;
use App\Events\Game\RoundFinished;
use App\Events\Game\ScoresCalculated;
use App\Models\Answer;
use App\Models\GameRoom;
use App\Models\Player;
use App\Models\Round;
use App\Models\Score;

final class RoundService
{
    public function __construct(
        private readonly ScoreEngine $scoreEngine,
        private readonly VotingService $votingService,
    ) {}

    public function startRound(GameRoom $room): Round
    {
        $roundNumber = $room->current_round + 1;

        $usedLetters = Round::where('game_room_id', $room->id)->pluck('letter')->all();
        $letter = ScoreEngine::drawLetter($usedLetters);

        $round = Round::create([
            'game_room_id' => $room->id,
            'round_number' => $roundNumber,
            'letter' => $letter,
            'status' => RoundStatus::Playing,
            'started_at' => now(),
        ]);

        $endsAt = now()->addSeconds($room->round_duration);

        $room->update([
            'status' => GameStatus::Playing,
            'current_round' => $roundNumber,
            'current_letter' => $letter,
            'round_started_at' => now(),
            'round_ends_at' => $endsAt,
        ]);

        broadcast(new LetterGenerated($room, $round));

        return $round;
    }

    public function submitAnswers(SubmitAnswersDTO $dto): void
    {
        $round = Round::findOrFail($dto->roundId);

        foreach ($dto->answers as $category => $value) {
            Answer::updateOrCreate(
                [
                    'round_id' => $round->id,
                    'player_id' => $dto->playerId,
                    'category' => $category,
                ],
                [
                    'value' => $value ?: null,
                    'submitted_at' => now(),
                ]
            );
        }
    }

    public function allPlayersSubmitted(GameRoom $room, Round $round): bool
    {
        $totalPlayers = $room->players()->count();

        if ($totalPlayers === 0) {
            return false;
        }

        $submittedPlayers = Answer::where('round_id', $round->id)
            ->distinct('player_id')
            ->count('player_id');

        return $submittedPlayers >= $totalPlayers;
    }

    public function stopRound(GameRoom $room, Player $player): void
    {
        // Atomic update: only proceeds if round is still in Playing status
        $affected = Round::where('game_room_id', $room->id)
            ->where('round_number', $room->current_round)
            ->where('status', RoundStatus::Playing)
            ->update([
                'status' => RoundStatus::Scoring,
                'stopped_by_player_id' => $player->id,
                'stopped_at' => now(),
            ]);

        if ($affected === 0) {
            return; // Already stopped by a concurrent request
        }

        $round = Round::where('game_room_id', $room->id)
            ->where('round_number', $room->current_round)
            ->firstOrFail();

        $room->update(['status' => GameStatus::Scoring]);

        broadcast(new PlayerStoppedRound($room, $player, $round));

        $this->votingService->startVoting($room, $round);
    }

    /**
     * @param  array<int, array<string, bool>>  $manualValidity  {playerId: {category: isValid}} from player votes
     */
    public function processScoring(GameRoom $room, Round $round, array $manualValidity = []): void
    {
        $room->update(['status' => GameStatus::Scoring]);

        $playerIds = $room->players()->pluck('players.id')->toArray();

        // Build answers matrix
        $playerAnswers = [];
        foreach ($playerIds as $playerId) {
            $playerAnswers[$playerId] = [];
            $answers = Answer::where('round_id', $round->id)
                ->where('player_id', $playerId)
                ->get();

            foreach ($answers as $answer) {
                $playerAnswers[$playerId][$answer->category] = $answer->value ?? '';
            }
        }

        // Calculate scores (manual validity from votes overrides auto-validation)
        $scoreResults = $this->scoreEngine->calculate($playerAnswers, $round->letter, $manualValidity);

        // Persist scores and update answers
        $this->persistRoundScores($room, $round, $scoreResults);

        $round->update([
            'status' => RoundStatus::Finished,
            'finished_at' => now(),
        ]);

        broadcast(new ScoresCalculated($room, $round, $scoreResults));

        // Check if game is finished
        if ($room->current_round >= $room->total_rounds) {
            broadcast(new RoundFinished($room, $round, true));
        } else {
            broadcast(new RoundFinished($room, $round, false));
        }
    }

    /**
     * @param  array<string, array{round_score: int, unique_count: int, has_perfect: bool, categories: array<string, array{value: string, valid: bool, unique: bool, points: int}>}>  $scoreResults
     */
    private function persistRoundScores(GameRoom $room, Round $round, array $scoreResults): void
    {
        foreach ($scoreResults as $playerId => $result) {
            // Update individual answer validity/uniqueness/points
            foreach ($result['categories'] as $category => $detail) {
                Answer::where('round_id', $round->id)
                    ->where('player_id', (int) $playerId)
                    ->where('category', $category)
                    ->update([
                        'is_valid' => $detail['valid'],
                        'is_unique' => $detail['unique'],
                        'points' => $detail['points'],
                    ]);
            }

            // Get previous cumulative score
            $previousScore = Score::where('game_room_id', $room->id)
                ->where('player_id', (int) $playerId)
                ->where('round_id', '!=', $round->id)
                ->orderBy('round_id', 'desc')
                ->value('cumulative_score') ?? 0;

            Score::create([
                'round_id' => $round->id,
                'player_id' => (int) $playerId,
                'game_room_id' => $room->id,
                'round_score' => $result['round_score'],
                'cumulative_score' => $previousScore + $result['round_score'],
                'has_perfect' => $result['has_perfect'],
                'unique_answers_count' => $result['unique_count'],
            ]);

            // Update pivot total score
            $room->players()->updateExistingPivot((int) $playerId, [
                'total_score' => $previousScore + $result['round_score'],
            ]);
        }
    }
}

<?php

namespace App\Domain\Game\Services;

use App\Domain\Game\Enums\Category;
use App\Domain\Game\Enums\GameStatus;
use App\Domain\Score\Services\ScoreEngine;
use App\Events\Game\VoteSubmitted;
use App\Events\Game\VotingStarted;
use App\Models\Answer;
use App\Models\GameRoom;
use App\Models\Player;
use App\Models\Round;
use App\Models\Vote;

final class VotingService
{
    public function __construct(
        private readonly ScoreEngine $scoreEngine,
    ) {}

    /**
     * Start the voting phase: compute auto-validation and broadcast all answers.
     */
    public function startVoting(GameRoom $room, Round $round): void
    {
        $room->update(['status' => GameStatus::Voting]);

        $players = $room->players()->get();
        $categories = Category::values();

        $playersAnswers = $players->map(function (Player $player) use ($round, $categories): array {
            $answers = [];

            foreach ($categories as $category) {
                $answer = Answer::where('round_id', $round->id)
                    ->where('player_id', $player->id)
                    ->where('category', $category)
                    ->first();

                $value = $answer?->value ?? '';
                $autoValid = ! empty($value) && $this->scoreEngine->validateAnswer($value, $round->letter, $category);

                $answers[$category] = [
                    'value' => $value,
                    'auto_valid' => $autoValid,
                ];
            }

            return [
                'player_id' => $player->id,
                'display_name' => $player->display_name,
                'avatar_color' => $player->avatar_color,
                'answers' => $answers,
            ];
        })->values()->all();

        broadcast(new VotingStarted($room, $round, $playersAnswers));
    }

    /**
     * Save a player's votes for the round. Returns true when all players have voted.
     *
     * @param  array<string, array<string, bool>>  $votes  {player_id: {category: is_valid}}
     */
    public function submitVotes(GameRoom $room, Round $round, Player $voter, array $votes): bool
    {
        foreach ($votes as $targetPlayerId => $categoryVotes) {
            if ((int) $targetPlayerId === $voter->id) {
                continue; // Cannot vote on your own answers
            }

            foreach ($categoryVotes as $category => $isValid) {
                Vote::updateOrCreate(
                    [
                        'round_id' => $round->id,
                        'voter_id' => $voter->id,
                        'answer_player_id' => (int) $targetPlayerId,
                        'category' => $category,
                    ],
                    ['is_valid' => (bool) $isValid],
                );
            }
        }

        $totalPlayers = $room->players()->count();
        $votedCount = $this->countVotedPlayers($room, $round);

        broadcast(new VoteSubmitted($room, $voter, $votedCount, $totalPlayers));

        return $votedCount >= $totalPlayers;
    }

    /**
     * Auto-submit votes for players who haven't voted yet using auto-validation results.
     */
    public function fillMissingVotes(GameRoom $room, Round $round): void
    {
        $players = $room->players()->get();

        foreach ($players as $player) {
            if ($this->hasVoted($round, $player)) {
                continue;
            }

            $otherPlayers = $players->where('id', '!=', $player->id);

            foreach ($otherPlayers as $target) {
                $categories = Category::values();

                foreach ($categories as $category) {
                    $answer = Answer::where('round_id', $round->id)
                        ->where('player_id', $target->id)
                        ->where('category', $category)
                        ->first();

                    $value = $answer?->value ?? '';
                    $autoValid = ! empty($value) && $this->scoreEngine->validateAnswer($value, $round->letter, $category);

                    Vote::updateOrCreate(
                        [
                            'round_id' => $round->id,
                            'voter_id' => $player->id,
                            'answer_player_id' => $target->id,
                            'category' => $category,
                        ],
                        ['is_valid' => $autoValid],
                    );
                }
            }
        }
    }

    /**
     * Build the final validity map from aggregated votes.
     * Majority rules; ties and unanswered answers default to auto-validation.
     *
     * @return array<int, array<string, bool>> {player_id: {category: is_valid}}
     */
    public function buildManualValidity(GameRoom $room, Round $round): array
    {
        $manualValidity = [];
        $playerIds = $room->players()->pluck('players.id')->toArray();
        $categories = Category::values();

        foreach ($playerIds as $playerId) {
            foreach ($categories as $category) {
                $votes = Vote::where('round_id', $round->id)
                    ->where('answer_player_id', $playerId)
                    ->where('category', $category)
                    ->get();

                if ($votes->isEmpty()) {
                    // No votes → fall back to auto-validation
                    $answer = Answer::where('round_id', $round->id)
                        ->where('player_id', $playerId)
                        ->where('category', $category)
                        ->first();

                    $value = $answer?->value ?? '';
                    $manualValidity[$playerId][$category] = ! empty($value)
                        && $this->scoreEngine->validateAnswer($value, $round->letter, $category);
                } else {
                    $validCount = $votes->where('is_valid', true)->count();
                    $invalidCount = $votes->where('is_valid', false)->count();
                    $manualValidity[$playerId][$category] = $validCount >= $invalidCount; // tie → valid
                }
            }
        }

        return $manualValidity;
    }

    public function hasVoted(Round $round, Player $voter): bool
    {
        return Vote::where('round_id', $round->id)
            ->where('voter_id', $voter->id)
            ->exists();
    }

    private function countVotedPlayers(GameRoom $room, Round $round): int
    {
        $playerIds = $room->players()->pluck('players.id');

        return Vote::where('round_id', $round->id)
            ->whereIn('voter_id', $playerIds)
            ->distinct('voter_id')
            ->count('voter_id');
    }
}

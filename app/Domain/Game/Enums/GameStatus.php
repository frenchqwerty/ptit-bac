<?php

namespace App\Domain\Game\Enums;

enum GameStatus: string
{
    case Waiting = 'waiting';
    case Countdown = 'countdown';
    case Playing = 'playing';
    case Voting = 'voting';
    case Scoring = 'scoring';
    case Finished = 'finished';

    public function label(): string
    {
        return match ($this) {
            self::Waiting => 'En attente',
            self::Countdown => 'Démarrage…',
            self::Playing => 'En cours',
            self::Voting => 'Vote en cours',
            self::Scoring => 'Calcul des scores',
            self::Finished => 'Terminée',
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Waiting => $next === self::Countdown,
            self::Countdown => $next === self::Playing,
            self::Playing => $next === self::Voting,
            self::Voting => $next === self::Scoring,
            self::Scoring => in_array($next, [self::Playing, self::Finished]),
            self::Finished => false,
        };
    }
}

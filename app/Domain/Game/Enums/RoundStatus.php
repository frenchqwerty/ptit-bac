<?php

namespace App\Domain\Game\Enums;

enum RoundStatus: string
{
    case Playing = 'playing';
    case Scoring = 'scoring';
    case Finished = 'finished';
}

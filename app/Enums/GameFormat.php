<?php

namespace App\Enums;

enum GameFormat: string
{
    case Singles = 'singles';
    case Doubles = 'doubles';

    /**
     * The number of players on each team for this format.
     */
    public function playersPerTeam(): int
    {
        return match ($this) {
            self::Singles => 1,
            self::Doubles => 2,
        };
    }
}

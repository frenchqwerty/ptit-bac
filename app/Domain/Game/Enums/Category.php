<?php

namespace App\Domain\Game\Enums;

enum Category: string
{
    case City = 'city';
    case Country = 'country';
    case FirstName = 'first_name';
    case Pokemon = 'pokemon';
    case Brand = 'brand';
    case Movie = 'movie';

    public function label(): string
    {
        return match ($this) {
            self::City => 'Ville',
            self::Country => 'Pays',
            self::FirstName => 'Prénom',
            self::Pokemon => 'Pokémon',
            self::Brand => 'Marque',
            self::Movie => 'Film',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::City => '🏙️',
            self::Country => '🌍',
            self::FirstName => '👤',
            self::Pokemon => '⚡',
            self::Brand => '🏷️',
            self::Movie => '🎬',
        };
    }

    /** @return array<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** @return array<string, string> */
    public static function labelsMap(): array
    {
        $map = [];
        foreach (self::cases() as $case) {
            $map[$case->value] = $case->label();
        }

        return $map;
    }
}

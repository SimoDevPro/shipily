<?php

namespace App\Enums;

enum ColisStatus: string
{
    case Enregistre = 'enregistre';
    case Ramasse = 'ramasse';
    case EnCours = 'en_cours';
    case Livre = 'livre';
    case Retourne = 'retourne';

    public function label(): string
    {
        return match ($this) {
            self::Enregistre => 'Enregistré',
            self::Ramasse => 'Ramassé',
            self::EnCours => 'En cours de livraison',
            self::Livre => 'Livré',
            self::Retourne => 'Retourné',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Enregistre => 'zinc',
            self::Ramasse => 'blue',
            self::EnCours => 'amber',
            self::Livre => 'green',
            self::Retourne => 'red',
        };
    }
}

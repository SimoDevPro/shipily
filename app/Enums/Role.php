<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Client = 'client';
    case Livreur = 'livreur';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Client => 'Client',
            self::Livreur => 'Livreur',
        };
    }
}

<?php

namespace App\Models;

use Database\Factories\AvisFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Avis extends Model
{
    /** @use HasFactory<AvisFactory> */
    use HasFactory;

    protected $fillable = [
        'colis_id',
        'livreur_id',
        'note',
        'commentaire',
    ];

    protected function casts(): array
    {
        return [
            'note' => 'integer',
        ];
    }

    public function colis(): BelongsTo
    {
        return $this->belongsTo(Colis::class);
    }

    public function livreur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'livreur_id');
    }
}

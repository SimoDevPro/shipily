<?php

namespace App\Models;

use App\Enums\ColisStatus;
use Database\Factories\ColisFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Colis extends Model
{
    /** @use HasFactory<ColisFactory> */
    use HasFactory;

    protected $fillable = [
        'code_suivi',
        'nom_destinataire',
        'telephone_destinataire',
        'adresse_destinataire',
        'ville_destinataire',
        'prix_colis',
        'frais_livraison',
        'statut',
        'client_id',
        'livreur_id',
    ];

    protected function casts(): array
    {
        return [
            'prix_colis' => 'decimal:2',
            'frais_livraison' => 'decimal:2',
            'statut' => ColisStatus::class,
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function livreur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'livreur_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ColisHistory::class);
    }

    public function avis(): HasOne
    {
        return $this->hasOne(Avis::class);
    }
}

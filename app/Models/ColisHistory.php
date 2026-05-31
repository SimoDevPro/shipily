<?php

namespace App\Models;

use App\Enums\ColisStatus;
use Database\Factories\ColisHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ColisHistory extends Model
{
    /** @use HasFactory<ColisHistoryFactory> */
    use HasFactory;

    protected $fillable = [
        'colis_id',
        'user_id',
        'statut',
        'localisation',
    ];

    protected function casts(): array
    {
        return [
            'statut' => ColisStatus::class,
        ];
    }

    public function colis(): BelongsTo
    {
        return $this->belongsTo(Colis::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

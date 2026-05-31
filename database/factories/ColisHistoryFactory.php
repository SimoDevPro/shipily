<?php

namespace Database\Factories;

use App\Enums\ColisStatus;
use App\Models\Colis;
use App\Models\ColisHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ColisHistory>
 */
class ColisHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'colis_id' => Colis::factory(),
            'user_id' => User::factory(),
            'statut' => ColisStatus::Enregistre,
            'localisation' => $this->faker->city(),
        ];
    }
}

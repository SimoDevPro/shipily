<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\Avis;
use App\Models\Colis;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Avis>
 */
class AvisFactory extends Factory
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
            'livreur_id' => User::factory()->create(['role' => Role::Livreur]),
            'note' => $this->faker->numberBetween(1, 5),
            'commentaire' => $this->faker->optional()->sentence(),
        ];
    }
}

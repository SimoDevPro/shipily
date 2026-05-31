<?php

namespace Database\Factories;

use App\Enums\ColisStatus;
use App\Enums\Role;
use App\Models\Colis;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Colis>
 */
class ColisFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code_suivi' => 'TRK'.$this->faker->unique()->numerify('########'),
            'nom_destinataire' => $this->faker->name(),
            'telephone_destinataire' => $this->faker->phoneNumber(),
            'adresse_destinataire' => $this->faker->streetAddress(),
            'ville_destinataire' => $this->faker->city(),
            'prix_colis' => $this->faker->randomFloat(2, 50, 5000),
            'frais_livraison' => $this->faker->randomElement([25, 30, 40, 50]),
            'statut' => ColisStatus::Enregistre,
            'client_id' => User::factory()->create(['role' => Role::Client]),
            'livreur_id' => null,
        ];
    }
}

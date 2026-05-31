<?php

namespace Database\Seeders;

use App\Enums\ColisStatus;
use App\Enums\Role;
use App\Models\Avis;
use App\Models\Colis;
use App\Models\ColisHistory;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@shipily.test',
            'role' => Role::Admin,
        ]);

        $client = User::factory()->create([
            'name' => 'Client E-commerce',
            'email' => 'client@shipily.test',
            'role' => Role::Client,
        ]);

        $livreur = User::factory()->create([
            'name' => 'Livreur Rapide',
            'email' => 'livreur@shipily.test',
            'role' => Role::Livreur,
        ]);

        // Create some colis for the client
        Colis::factory(5)->create([
            'client_id' => $client->id,
            'livreur_id' => null,
        ]);

        // Create some colis assigned to the livreur
        $colisLivres = Colis::factory(3)->create([
            'client_id' => $client->id,
            'livreur_id' => $livreur->id,
            'statut' => ColisStatus::Livre,
        ]);

        // Add history and avis for delivered packages
        foreach ($colisLivres as $colis) {
            ColisHistory::factory()->create([
                'colis_id' => $colis->id,
                'user_id' => $livreur->id,
                'statut' => ColisStatus::Livre,
            ]);

            Avis::factory()->create([
                'colis_id' => $colis->id,
                'livreur_id' => $livreur->id,
            ]);
        }
    }
}

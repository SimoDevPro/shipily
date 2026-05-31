<?php

use App\Enums\ColisStatus;
use App\Enums\Role;
use App\Models\Colis;
use App\Models\User;
use Livewire\Volt\Volt;

test('it renders the admin colis manager component', function () {
    $admin = User::factory()->create(['role' => Role::Admin->value]);
    $this->actingAs($admin);

    Volt::test('admin.colis-manager')
        ->assertSee('Gestion des Colis');
});

test('it can assign a livreur to a colis', function () {
    $admin = User::factory()->create(['role' => Role::Admin->value]);
    $livreur = User::factory()->create(['role' => Role::Livreur->value]);

    $colis = Colis::factory()->create([
        'livreur_id' => null,
        'statut' => ColisStatus::Enregistre->value,
    ]);

    $this->actingAs($admin);

    Volt::test('admin.colis-manager')
        ->call('openAssignModal', $colis->id)
        ->assertSet('selectedColisId', $colis->id)
        ->assertSet('selectedLivreurId', null)
        ->set('selectedLivreurId', $livreur->id)
        ->call('assignLivreur')
        ->assertHasNoErrors();

    $colis->refresh();

    expect($colis->livreur_id)->toBe($livreur->id);

    $this->assertDatabaseHas('colis_histories', [
        'colis_id' => $colis->id,
        'user_id' => $admin->id,
        'statut' => ColisStatus::Enregistre->value,
    ]);
});

<?php

use App\Enums\ColisStatus;
use App\Models\Colis;
use Illuminate\Support\Str;
use function Livewire\Volt\{state, rules, layout};

layout('components.layouts.app');

state([
    'nom_destinataire' => '',
    'telephone_destinataire' => '',
    'adresse_destinataire' => '',
    'ville_destinataire' => '',
    'prix_colis' => '',
    'is_success' => false,
]);

rules([
    'nom_destinataire' => 'required|string|max:255',
    'telephone_destinataire' => 'required|string|max:20',
    'adresse_destinataire' => 'required|string|max:255',
    'ville_destinataire' => 'required|string|max:255',
    'prix_colis' => 'required|numeric|min:0',
]);

$save = function () {
    $this->is_success = false;
    $validated = $this->validate();

    Colis::create([
        'code_suivi' => 'TRK' . random_int(10000000, 99999999),
        'nom_destinataire' => $validated['nom_destinataire'],
        'telephone_destinataire' => $validated['telephone_destinataire'],
        'adresse_destinataire' => $validated['adresse_destinataire'],
        'ville_destinataire' => $validated['ville_destinataire'],
        'prix_colis' => $validated['prix_colis'],
        'frais_livraison' => 0, // Set default or calculate later
        'statut' => ColisStatus::Enregistre,
        'client_id' => auth()->id(),
    ]);

    $this->reset(['nom_destinataire', 'telephone_destinataire', 'adresse_destinataire', 'ville_destinataire', 'prix_colis']);
    $this->is_success = true;
};

?>

<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1">Créer un nouveau colis</flux:heading>
            <flux:subheading size="lg" class="mb-6">Remplissez les informations du destinataire pour enregistrer un nouveau colis.</flux:subheading>
        </div>
    </div>

    @if ($is_success)
        <flux:callout variant="success" icon="check-circle" class="mb-6">
            <flux:heading>Colis créé avec succès !</flux:heading>
            <flux:subheading>Le colis a été enregistré et un code de suivi lui a été attribué.</flux:subheading>
        </flux:callout>
    @endif

    <div class="max-w-2xl">
        <form wire:submit="save" class="space-y-6">
            <flux:card>
                <div class="space-y-4">
                    <flux:field>
                        <flux:label>Nom du destinataire</flux:label>
                        <flux:input wire:model="nom_destinataire" placeholder="Ex: Jean Dupont" />
                        <flux:error name="nom_destinataire" />
                    </flux:field>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>Téléphone du destinataire</flux:label>
                            <flux:input wire:model="telephone_destinataire" placeholder="Ex: 0612345678" />
                            <flux:error name="telephone_destinataire" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Ville du destinataire</flux:label>
                            <flux:input wire:model="ville_destinataire" placeholder="Ex: Casablanca" />
                            <flux:error name="ville_destinataire" />
                        </flux:field>
                    </div>

                    <flux:field>
                        <flux:label>Adresse du destinataire</flux:label>
                        <flux:textarea wire:model="adresse_destinataire" rows="3" placeholder="Ex: 123 Rue de la Poste, Quartier X" />
                        <flux:error name="adresse_destinataire" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Prix du colis (MAD)</flux:label>
                        <flux:input wire:model="prix_colis" type="number" step="0.01" placeholder="0.00" />
                        <flux:error name="prix_colis" />
                    </flux:field>
                </div>

                <div class="mt-6 flex justify-end">
                    <flux:button type="submit" variant="primary" icon="plus">Enregistrer le colis</flux:button>
                </div>
            </flux:card>
        </form>
    </div>
</div>

<?php

use App\Enums\Role;
use App\Enums\ColisStatus;
use App\Models\Colis;
use App\Models\ColisHistory;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    // CRUD / Assign States
    public ?Colis $editingColis = null;
    public ?int $selectedColisId = null;
    public ?int $selectedLivreurId = null;

    // Form fields
    public ?int $client_id = null;
    public string $nom_destinataire = '';
    public string $telephone_destinataire = '';
    public string $adresse_destinataire = '';
    public string $ville_destinataire = '';
    public string $prix_colis = '0';
    public string $statut = 'enregistre';

    public function with(): array
    {
        return [
            'colis' => Colis::with(['client', 'livreur'])
                ->when($this->search, function($query) {
                    $query->where('code_suivi', 'like', '%'.$this->search.'%')
                          ->orWhere('nom_destinataire', 'like', '%'.$this->search.'%');
                })
                ->latest()
                ->paginate(10),
            'clients' => User::where('role', Role::Client)->orderBy('name')->get(),
            'livreurs' => User::where('role', Role::Livreur)->orderBy('name')->get(),
        ];
    }

    public function create()
    {
        $this->reset(['editingColis', 'client_id', 'nom_destinataire', 'telephone_destinataire', 'adresse_destinataire', 'ville_destinataire', 'prix_colis', 'statut']);
        $this->modal('colis-modal')->show();
    }

    public function edit(Colis $colis)
    {
        $this->editingColis = $colis;
        $this->client_id = $colis->client_id;
        $this->nom_destinataire = $colis->nom_destinataire;
        $this->telephone_destinataire = $colis->telephone_destinataire;
        $this->adresse_destinataire = $colis->adresse_destinataire;
        $this->ville_destinataire = $colis->ville_destinataire;
        $this->prix_colis = (string)$colis->prix_colis;
        $this->statut = $colis->statut->value;

        $this->modal('colis-modal')->show();
    }

    public function save()
    {
        $validated = $this->validate([
            'client_id' => 'required|exists:users,id',
            'nom_destinataire' => 'required|string|max:255',
            'telephone_destinataire' => 'required|string|max:20',
            'adresse_destinataire' => 'required|string|max:255',
            'ville_destinataire' => 'required|string|max:255',
            'prix_colis' => 'required|numeric|min:0',
            'statut' => 'required|string',
        ]);

        if ($this->editingColis) {
            $this->editingColis->update($validated);
            Flux::toast('Colis mis à jour avec succès.');
        } else {
            Colis::create([
                ...$validated,
                'code_suivi' => 'TRK' . random_int(10000000, 99999999),
                'frais_livraison' => 0, // Admin can adjust this later or we can add a field
            ]);
            Flux::toast('Nouveau colis créé avec succès.');
        }

        $this->modal('colis-modal')->close();
        $this->resetPage();
    }

    public function delete(Colis $colis)
    {
        $colis->delete();
        Flux::toast('Colis supprimé.');
    }

    public function openAssignModal(Colis $colis)
    {
        $this->selectedColisId = $colis->id;
        $this->selectedLivreurId = $colis->livreur_id;
        $this->modal('assign-livreur-modal')->show();
    }

    public function assignLivreur()
    {
        $this->validate([
            'selectedLivreurId' => 'required|exists:users,id',
        ]);

        $colis = Colis::findOrFail($this->selectedColisId);
        $colis->update(['livreur_id' => $this->selectedLivreurId]);

        ColisHistory::create([
            'colis_id' => $colis->id,
            'user_id' => Auth::id(),
            'statut' => $colis->statut->value,
            'localisation' => 'Assignation livreur par Admin',
        ]);

        $this->modal('assign-livreur-modal')->close();
        Flux::toast('Livreur assigné avec succès.');
    }
}; ?>

<div class="animate-fade-in">
    <flux:header class="flex justify-between items-start">
        <div>
            <flux:heading size="xl" level="1">Gestion des Colis</flux:heading>
            <flux:subheading>Suivez l'état des livraisons et gérez les flux de colis.</flux:subheading>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="create">Nouveau Colis</flux:button>
    </flux:header>

    <div class="mt-6">
        <div class="mb-6">
            <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Rechercher par code ou nom..." class="max-w-xs" />
        </div>

        <flux:table :paginate="$colis">
            <flux:table.columns>
                <flux:table.column>Colis</flux:table.column>
                <flux:table.column>Client</flux:table.column>
                <flux:table.column>Destinataire</flux:table.column>
                <flux:table.column>Statut</flux:table.column>
                <flux:table.column>Livreur</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>
            
            <flux:table.rows>
                @foreach ($colis as $item)
                    <flux:table.row :key="$item->id">
                        <flux:table.cell>
                            <div class="flex flex-col">
                                <span class="font-medium text-zinc-900 dark:text-white">{{ $item->code_suivi }}</span>
                                <span class="text-xs text-zinc-500">{{ $item->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </flux:table.cell>
                        
                        <flux:table.cell>
                            <div class="flex items-center gap-3">
                                <flux:avatar size="sm" :name="$item->client?->name" />
                                <span class="font-medium">{{ $item->client?->name ?? 'N/A' }}</span>
                            </div>
                        </flux:table.cell>
                        
                        <flux:table.cell>
                            <div class="flex flex-col">
                                <span class="font-medium">{{ $item->nom_destinataire }}</span>
                                <span class="text-xs text-zinc-500">{{ $item->ville_destinataire }}</span>
                            </div>
                        </flux:table.cell>
                        
                        <flux:table.cell>
                            <flux:badge :color="$item->statut->color()" size="sm" inset="top bottom">
                                {{ $item->statut->label() }}
                            </flux:badge>
                        </flux:table.cell>
                        
                        <flux:table.cell>
                            @if ($item->livreur)
                                <div class="flex items-center gap-3">
                                    <flux:avatar size="sm" :name="$item->livreur->name" />
                                    <span class="font-medium">{{ $item->livreur->name }}</span>
                                </div>
                            @else
                                <span class="text-zinc-400 text-sm italic">Non assigné</span>
                            @endif
                        </flux:table.cell>
                        
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:button icon="user-plus" variant="ghost" size="sm" wire:click="openAssignModal({{ $item->id }})" />
                                <flux:button icon="pencil-square" variant="ghost" size="sm" wire:click="edit({{ $item->id }})" />
                                <flux:button icon="trash" variant="ghost" size="sm" wire:click="delete({{ $item->id }})" wire:confirm="Supprimer ce colis ?" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>

    {{-- Create/Edit Model --}}
    <flux:modal name="colis-modal" class="min-w-[28rem]">
        <form wire:submit="save">
            <div>
                <flux:heading size="lg">{{ $editingColis ? 'Modifier le colis' : 'Nouveau Colis' }}</flux:heading>
                <flux:subheading>{{ $editingColis ? 'Mettre à jour les détails de l\'expédition.' : 'Enregistrer une nouvelle expédition manuellement.' }}</flux:subheading>
            </div>

            <div class="space-y-6 mt-6">
                <flux:select wire:model="client_id" label="Client (Expéditeur)" required placeholder="Sélectionnez un client">
                    @foreach($clients as $client)
                        <flux:select.option value="{{ $client->id }}">{{ $client->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="nom_destinataire" label="Destinataire" placeholder="Nom complet" required />
                    <flux:input wire:model="telephone_destinataire" label="Téléphone" placeholder="06..." required />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="ville_destinataire" label="Ville" placeholder="Casablanca" required />
                    <flux:input wire:model="prix_colis" type="number" step="0.01" label="Prix (DH)" required />
                </div>

                <flux:textarea wire:model="adresse_destinataire" label="Adresse complète" required />

                <flux:select wire:model="statut" label="Statut">
                    @foreach(App\Enums\ColisStatus::cases() as $s)
                        <flux:select.option value="{{ $s->value }}">{{ $s->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="flex mt-8 gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Annuler</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ $editingColis ? 'Enregistrer les modifications' : 'Créer le colis' }}</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Assign Modal --}}
    <flux:modal name="assign-livreur-modal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Assigner un livreur</flux:heading>
                <flux:subheading>Choisissez un livreur pour acheminer ce colis.</flux:subheading>
            </div>

            <flux:select wire:model="selectedLivreurId" placeholder="Sélectionnez un livreur">
                @foreach ($livreurs as $liv)
                    <flux:select.option value="{{ $liv->id }}">{{ $liv->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <div class="flex space-x-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Annuler</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="assignLivreur">Assigner</flux:button>
            </div>
        </div>
    </flux:modal>
</div>

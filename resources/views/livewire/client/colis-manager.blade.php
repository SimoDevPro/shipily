<?php

use App\Models\Colis;
use App\Enums\ColisStatus;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    public ?Colis $editingColis = null;

    // Form fields
    public string $nom_destinataire = '';
    public string $telephone_destinataire = '';
    public string $adresse_destinataire = '';
    public string $ville_destinataire = '';
    public string $prix_colis = '0';

    public function with(): array
    {
        return [
            'colis' => Auth::user()->colisAsClient()
                ->when($this->search, function($query) {
                    $query->where(function($q) {
                        $q->where('code_suivi', 'like', '%'.$this->search.'%')
                          ->orWhere('nom_destinataire', 'like', '%'.$this->search.'%');
                    });
                })
                ->latest()
                ->paginate(10),
        ];
    }

    public function edit(Colis $colis)
    {
        // Security check
        if ($colis->client_id !== Auth::id()) {
            abort(403);
        }

        // Status check: Only editable if NOT in delivery/final status
        if (!in_array($colis->statut, [ColisStatus::Enregistre, ColisStatus::Ramasse])) {
            Flux::toast('Impossible de modifier un colis déjà en cours de livraison.', variant: 'danger');
            return;
        }

        $this->editingColis = $colis;
        $this->nom_destinataire = $colis->nom_destinataire;
        $this->telephone_destinataire = $colis->telephone_destinataire;
        $this->adresse_destinataire = $colis->adresse_destinataire;
        $this->ville_destinataire = $colis->ville_destinataire;
        $this->prix_colis = (string)$colis->prix_colis;

        $this->modal('edit-colis-modal')->show();
    }

    public function save()
    {
        $validated = $this->validate([
            'nom_destinataire' => 'required|string|max:255',
            'telephone_destinataire' => 'required|string|max:20',
            'adresse_destinataire' => 'required|string|max:255',
            'ville_destinataire' => 'required|string|max:255',
            'prix_colis' => 'required|numeric|min:0',
        ]);

        if ($this->editingColis) {
            // Re-verify status before saving
            if (!in_array($this->editingColis->statut, [ColisStatus::Enregistre, ColisStatus::Ramasse])) {
                Flux::toast('Le statut du colis a changé, modification impossible.', variant: 'danger');
                $this->modal('edit-colis-modal')->close();
                return;
            }

            $this->editingColis->update($validated);
            Flux::toast('Colis mis à jour avec succès.');
        }

        $this->modal('edit-colis-modal')->close();
    }

    public function delete(Colis $colis)
    {
        if ($colis->client_id !== Auth::id()) abort(403);

        if ($colis->statut !== ColisStatus::Enregistre) {
            Flux::toast('Seuls les colis en attente peuvent être supprimés.', variant: 'danger');
            return;
        }

        $colis->delete();
        Flux::toast('Colis supprimé.');
    }
}; ?>

<div class="animate-fade-in">
    <flux:header class="flex justify-between items-start">
        <div>
            <flux:heading size="xl" level="1">Mes Colis</flux:heading>
            <flux:subheading>Gérez vos expéditions et suivez leur progression.</flux:subheading>
        </div>
        <flux:button variant="primary" icon="plus" href="{{ route('client.colis.create') }}" wire:navigate>Nouveau Colis</flux:button>
    </flux:header>

    <div class="mt-6">
        <div class="mb-6">
            <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Rechercher par code ou nom..." class="max-w-xs" />
        </div>

        <flux:table :paginate="$colis">
            <flux:table.columns>
                <flux:table.column>Colis</flux:table.column>
                <flux:table.column>Destinataire</flux:table.column>
                <flux:table.column>Prix</flux:table.column>
                <flux:table.column>Statut</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>
            
            <flux:table.rows>
                @foreach ($colis as $item)
                    @php
                        $isEditable = in_array($item->statut, [App\Enums\ColisStatus::Enregistre, App\Enums\ColisStatus::Ramasse]);
                        $isDeletable = $item->statut === App\Enums\ColisStatus::Enregistre;
                    @endphp
                    <flux:table.row :key="$item->id">
                        <flux:table.cell>
                            <div class="flex flex-col">
                                <span class="font-medium text-zinc-900 dark:text-white">{{ $item->code_suivi }}</span>
                                <span class="text-xs text-zinc-500">{{ $item->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </flux:table.cell>
                        
                        <flux:table.cell>
                            <div class="flex flex-col">
                                <span class="font-medium">{{ $item->nom_destinataire }}</span>
                                <span class="text-xs text-zinc-500">{{ $item->ville_destinataire }}</span>
                            </div>
                        </flux:table.cell>
                        
                        <flux:table.cell>
                            <span class="font-medium">{{ number_format($item->prix_colis, 2) }} DH</span>
                        </flux:table.cell>
                        
                        <flux:table.cell>
                            <flux:badge :color="$item->statut->color()" size="sm" inset="top bottom">
                                {{ $item->statut->label() }}
                            </flux:badge>
                        </flux:table.cell>
                        
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:button icon="eye" variant="ghost" size="sm" href="{{ route('tracking', ['codeSuivi' => $item->code_suivi]) }}" wire:navigate />
                                
                                @if($isEditable)
                                    <flux:button icon="pencil-square" variant="ghost" size="sm" wire:click="edit({{ $item->id }})" />
                                @endif

                                @if($isDeletable)
                                    <flux:button icon="trash" variant="ghost" size="sm" wire:click="delete({{ $item->id }})" wire:confirm="Supprimer ce colis ?" />
                                @endif
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>

    {{-- Edit Modal --}}
    <flux:modal name="edit-colis-modal" class="min-w-[28rem]">
        <form wire:submit="save">
            <div>
                <flux:heading size="lg">Modifier le colis</flux:heading>
                <flux:subheading>Mettez à jour les informations de livraison avant l'expédition.</flux:subheading>
            </div>

            <div class="space-y-6 mt-6">
                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="nom_destinataire" label="Destinataire" placeholder="Nom complet" required />
                    <flux:input wire:model="telephone_destinataire" label="Téléphone" placeholder="06..." required />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="ville_destinataire" label="Ville" placeholder="Casablanca" required />
                    <flux:input wire:model="prix_colis" type="number" step="0.01" label="Prix (DH)" required />
                </div>

                <flux:textarea wire:model="adresse_destinataire" label="Adresse complète" required />
            </div>

            <div class="flex mt-8 gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Annuler</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Enregistrer les modifications</flux:button>
            </div>
        </form>
    </flux:modal>
</div>

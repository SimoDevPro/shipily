<?php

use App\Models\Colis;
use App\Models\ColisHistory;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.app')] class extends Component {
    public function with(): array
    {
        return [
            'colisList' => Colis::where('livreur_id', Auth::id())
                ->whereNotIn('statut', ['livre', 'retourne'])
                ->orderBy('created_at', 'desc')
                ->get(),
        ];
    }

    public function updateStatus(int $colisId, string $newStatus): void
    {
        $colis = Colis::where('livreur_id', Auth::id())->findOrFail($colisId);

        $validStatuses = ['enregistre', 'ramasse', 'en_cours', 'livre', 'retourne'];
        
        if (! in_array($newStatus, $validStatuses)) {
            return;
        }

        $colis->update(['statut' => $newStatus]);

        ColisHistory::create([
            'colis_id' => $colis->id,
            'user_id' => Auth::id(),
            'statut' => $newStatus,
        ]);
    }
}; ?>

<div class="space-y-4">
    @forelse ($colisList as $colis)
        <flux:card class="flex flex-col gap-4">
            {{-- Header --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <flux:icon.cube class="w-5 h-5 text-zinc-500" />
                    <span class="font-medium text-zinc-900 dark:text-white">{{ $colis->code_suivi }}</span>
                </div>
                <flux:badge :color="$colis->statut->color()" size="sm">{{ $colis->statut->label() }}</flux:badge>
            </div>

            <flux:separator variant="subtle" />

            {{-- Body --}}
            <div class="flex flex-col gap-3 text-sm text-zinc-600 dark:text-zinc-400">
                <div class="flex items-start gap-2">
                    <flux:icon.user class="w-4 h-4 mt-0.5 text-zinc-400" />
                    <span>{{ $colis->nom_destinataire }}</span>
                </div>
                
                <div class="flex items-start gap-2">
                    <flux:icon.phone class="w-4 h-4 mt-0.5 text-zinc-400" />
                    <a href="tel:{{ $colis->telephone_destinataire }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                        {{ $colis->telephone_destinataire }}
                    </a>
                </div>
                
                <div class="flex items-start gap-2">
                    <flux:icon.map-pin class="w-4 h-4 mt-0.5 text-zinc-400" />
                    <span>{{ $colis->adresse_destinataire }}, {{ $colis->ville_destinataire }}</span>
                </div>
                
                <div class="flex items-start gap-2">
                    <flux:icon.banknotes class="w-4 h-4 mt-0.5 text-zinc-400" />
                    <span class="font-medium text-zinc-900 dark:text-white">
                        {{ number_format($colis->prix_colis + $colis->frais_livraison, 2) }} MAD
                    </span>
                </div>
            </div>

            {{-- Actions --}}
            <div class="pt-2">
                @if ($colis->statut->value === 'enregistre')
                    <flux:button wire:click="updateStatus({{ $colis->id }}, 'ramasse')" variant="primary" class="w-full">
                        Ramasser le colis
                    </flux:button>
                @elseif ($colis->statut->value === 'ramasse')
                    <flux:button wire:click="updateStatus({{ $colis->id }}, 'en_cours')" variant="primary" class="w-full">
                        Démarrer la livraison
                    </flux:button>
                @elseif ($colis->statut->value === 'en_cours')
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <flux:button wire:click="updateStatus({{ $colis->id }}, 'livre')" variant="primary" class="w-full sm:w-1/2">
                            Livré
                        </flux:button>
                        <flux:button wire:click="updateStatus({{ $colis->id }}, 'retourne')" variant="danger" class="w-full sm:w-1/2">
                            Retourné
                        </flux:button>
                    </div>
                @endif
            </div>
        </flux:card>
    @empty
        <flux:card class="flex flex-col items-center justify-center p-8 text-center gap-4">
            <flux:icon.inbox class="w-12 h-12 text-zinc-300 dark:text-zinc-600" />
            <div class="space-y-1">
                <flux:heading size="lg">Aucun colis actif</flux:heading>
                <flux:subheading>Vous n'avez aucun colis en cours de livraison pour le moment.</flux:subheading>
            </div>
        </flux:card>
    @endforelse
</div>

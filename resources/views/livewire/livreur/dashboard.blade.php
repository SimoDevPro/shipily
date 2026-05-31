<?php

use App\Models\Colis;
use App\Enums\ColisStatus;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\Attributes\Computed;

new #[Layout('components.layouts.app')] class extends Component {
    #[Computed]
    public function stats()
    {
        $counts = auth()->user()->colisAsLivreur()
            ->toBase()
            ->selectRaw('statut, count(*) as count')
            ->groupBy('statut')
            ->pluck('count', 'statut');

        return [
            'ramasser' => $counts->get(ColisStatus::Enregistre->value, 0),
            'en_cours' => $counts->get(ColisStatus::Ramasse->value, 0) + $counts->get(ColisStatus::EnCours->value, 0),
            'livre' => $counts->get(ColisStatus::Livre->value, 0),
            'active_colis' => auth()->user()->colisAsLivreur()
                ->whereNotIn('statut', [ColisStatus::Livre, ColisStatus::Retourne])
                ->latest()
                ->get(),
        ];
    }
}; ?>

<div class="max-w-md mx-auto animate-fade-in pb-20">
    <div class="mb-8">
        <flux:heading size="xl" level="1">Mes Missions</flux:heading>
        <flux:subheading>Vos colis à livrer aujourd'hui.</flux:subheading>
    </div>

    <div class="flex gap-2 mb-8 overflow-x-auto pb-2 scrollbar-hide no-scrollbar">
        <flux:badge size="sm" variant="outline" class="whitespace-nowrap rounded-full px-4">À récupérer ({{ $this->stats['ramasser'] }})</flux:badge>
        <flux:badge size="sm" variant="outline" class="whitespace-nowrap rounded-full px-4">En cours ({{ $this->stats['en_cours'] }})</flux:badge>
        <flux:badge size="sm" variant="outline" class="whitespace-nowrap rounded-full px-4">Livrés ({{ $this->stats['livre'] }})</flux:badge>
    </div>

    <div class="space-y-5">
        @forelse($this->stats['active_colis'] as $colis)
            <flux:card class="p-5 border-none shadow-sm bg-white dark:bg-zinc-900 rounded-2xl">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <div class="text-[10px] text-zinc-400 font-bold uppercase tracking-widest mb-1">{{ $colis->code_suivi }}</div>
                        <flux:heading size="lg">{{ $colis->nom_destinataire }}</flux:heading>
                    </div>
                    <flux:badge size="sm" variant="outline" class="rounded-full">{{ $colis->statut->label() }}</flux:badge>
                </div>
                
                <div class="space-y-3 mb-6">
                    <div class="flex items-start gap-3">
                        <div class="mt-1 p-1.5 bg-zinc-100 dark:bg-zinc-800 rounded-md">
                            <flux:icon.map-pin variant="mini" class="size-3.5 text-zinc-500" />
                        </div>
                        <div class="text-sm text-zinc-600 dark:text-zinc-400">
                            {{ $colis->adresse_destinataire }}, {{ $colis->ville_destinataire }}
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="p-1.5 bg-zinc-100 dark:bg-zinc-800 rounded-md">
                            <flux:icon.phone variant="mini" class="size-3.5 text-zinc-500" />
                        </div>
                        <div class="text-sm text-zinc-600 dark:text-zinc-400">{{ $colis->telephone_destinataire }}</div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="p-1.5 bg-zinc-100 dark:bg-zinc-800 rounded-md">
                            <flux:icon.banknotes variant="mini" class="size-3.5 text-zinc-500" />
                        </div>
                        <div class="text-sm font-bold text-zinc-900 dark:text-white">{{ number_format($colis->prix_colis + $colis->frais_livraison, 2) }} DH <span class="text-xs font-normal text-zinc-500 ml-1">(Total à encaisser)</span></div>
                    </div>
                </div>

                <div class="flex gap-2">
                    <flux:button href="tel:{{ $colis->telephone_destinataire }}" class="flex-1 rounded-xl" icon="phone">Appeler</flux:button>
                    <flux:button href="{{ route('livreur.colis') }}" variant="primary" class="flex-1 rounded-xl" wire:navigate>Gérer</flux:button>
                </div>
            </flux:card>
        @empty
            <div class="text-center py-12 px-6">
                <div class="inline-flex p-4 bg-zinc-100 dark:bg-zinc-800 rounded-full mb-4">
                    <flux:icon.check-circle class="size-8 text-zinc-400" />
                </div>
                <flux:heading size="lg">Aucune mission</flux:heading>
                <flux:text class="mt-2">Vous n'avez aucun colis assigné pour le moment.</flux:text>
            </div>
        @endforelse
    </div>
</div>

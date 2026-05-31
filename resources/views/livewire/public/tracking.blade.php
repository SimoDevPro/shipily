<?php

use App\Models\Colis;
use App\Models\Avis;
use App\Enums\ColisStatus;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Log;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Url]
    public ?string $codeSuivi = null;
    
    public ?Colis $colis = null;

    // Feedback Form
    public int|string $note = 5;
    public ?string $commentaire = null;
    public bool $avisSubmitted = false;

    public function mount(?string $codeSuivi = null)
    {
        if ($codeSuivi) {
            $this->codeSuivi = $codeSuivi;
        }
        
        if ($this->codeSuivi) {
            $this->loadColis();
        }
    }

    public function search()
    {
        $this->validate([
            'codeSuivi' => 'required|string',
        ], [
            'codeSuivi.required' => 'Le code de suivi est requis.',
        ]);
        
        $this->loadColis();
    }

    private function loadColis()
    {
        $this->colis = Colis::with(['histories' => function($q) {
            $q->orderBy('created_at', 'desc');
        }, 'livreur', 'avis'])->where('code_suivi', $this->codeSuivi)->first();

        if ($this->colis && $this->colis->avis) {
            $this->avisSubmitted = true;
        } else {
            $this->avisSubmitted = false;
        }
    }

    public function submitAvis()
    {
        if (!$this->colis || $this->colis->statut !== ColisStatus::Livre || $this->avisSubmitted) {
            return;
        }

        $this->validate([
            'note' => 'required|integer|min:1|max:5',
            'commentaire' => 'nullable|string|max:500',
        ], [
            'note.required' => 'Veuillez sélectionner une note.',
            'note.integer' => 'La note doit être un nombre.',
            'note.min' => 'La note minimum est 1.',
            'note.max' => 'La note maximum est 5.',
            'commentaire.max' => 'Le commentaire ne doit pas dépasser 500 caractères.',
        ]);

        Avis::create([
            'colis_id' => $this->colis->id,
            'livreur_id' => $this->colis->livreur_id,
            'note' => (int) $this->note,
            'commentaire' => $this->commentaire,
        ]);

        $this->avisSubmitted = true;
        $this->colis->load('avis');
        
        try {
            \Flux::toast('Merci pour votre retour !');
        } catch (\Throwable $th) {
            // Toast might not be configured, ignore
        }
    }
}
?>

<div class="flex flex-col gap-6 w-full max-w-md mx-auto">
    <!-- Header -->
    <div class="flex flex-col items-center gap-2 text-center">
        <flux:heading size="xl">Suivi de Colis</flux:heading>
        <flux:text>Entrez votre code de suivi pour localiser votre colis.</flux:text>
    </div>

    <!-- Search Form -->
    <form wire:submit="search" class="flex gap-2">
        <div class="flex-1">
            <flux:input wire:model="codeSuivi" placeholder="Ex: TRK83749382" icon="magnifying-glass" class="rounded-xl" />
        </div>
        <flux:button type="submit" variant="primary" class="rounded-xl">Suivre</flux:button>
    </form>

    @if($codeSuivi && !$colis)
        <flux:callout variant="danger" icon="exclamation-triangle" class="rounded-xl animate-fade-in">
            Nous n'avons pas pu trouver de colis avec le code "{{ $codeSuivi }}". Veuillez vérifier et réessayer.
        </flux:callout>
    @elseif($colis)
        <!-- Package Info -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-6 shadow-sm flex flex-col gap-6 animate-fade-in">
            <div class="flex justify-between items-start gap-4">
                <div>
                    <div class="text-[10px] text-zinc-400 font-bold uppercase tracking-widest mb-1">{{ $colis->code_suivi }}</div>
                    <flux:heading size="lg">{{ $colis->nom_destinataire }}</flux:heading>
                    <flux:text class="text-sm mt-1 flex items-center gap-1.5 text-zinc-500">
                        <flux:icon name="map-pin" variant="mini" class="size-4 text-zinc-400"/>
                        {{ $colis->ville_destinataire }}
                    </flux:text>
                </div>
                <flux:badge size="sm" variant="outline" class="rounded-full px-3">{{ $colis->statut->label() }}</flux:badge>
            </div>
            
            <flux:separator variant="subtle" />

            <!-- Timeline -->
            <div class="flex flex-col gap-4">
                <flux:heading size="sm" class="text-zinc-500 uppercase tracking-wider font-bold text-[10px]">Historique de livraison</flux:heading>
                <div class="relative pl-6 space-y-8 before:absolute before:left-[11px] before:top-2 before:bottom-2 before:w-[1.5px] before:bg-zinc-100 dark:before:bg-zinc-800">
                    @foreach($colis->histories as $history)
                        <div class="relative">
                            <div class="absolute -left-[18.5px] mt-1.5 h-4 w-4 rounded-full border-[3px] border-white dark:border-zinc-900 {{ $loop->first ? 'bg-zinc-900 dark:bg-white' : 'bg-zinc-200 dark:bg-zinc-700' }}"></div>
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold {{ $loop->first ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-500 dark:text-zinc-400' }}">
                                    {{ $history->statut->label() }}
                                </span>
                                <span class="text-[11px] text-zinc-400 mt-0.5 tracking-tight">{{ $history->created_at->translatedFormat('d M Y à H:i') }}</span>
                                @if($history->localisation)
                                    <div class="inline-flex items-center gap-1 mt-1.5 px-2 py-0.5 bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-100 dark:border-zinc-800 rounded text-[10px] text-zinc-500 w-fit">
                                        <flux:icon name="map-pin" variant="mini" class="size-3 text-zinc-400"/> 
                                        {{ $history->localisation }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Feedback Form -->
        @if($colis->statut === \App\Enums\ColisStatus::Livre)
            <div class="animate-fade-in delay-150">
                @if($avisSubmitted)
                    <flux:callout variant="success" icon="check-circle" class="rounded-2xl">
                        Merci pour votre retour ! Votre avis a bien été enregistré.
                    </flux:callout>
                @else
                    <form wire:submit="submitAvis" class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-6 shadow-sm flex flex-col gap-6">
                        <div class="flex flex-col gap-1">
                            <flux:heading size="md">Évaluer la livraison</flux:heading>
                            <flux:text class="text-sm text-zinc-500">Comment s'est passée votre expérience avec {{ $colis->livreur?->name ?? 'notre livreur' }} ?</flux:text>
                        </div>
                        
                        <flux:radio.group wire:model="note" variant="segmented" class="p-1 bg-zinc-50 dark:bg-zinc-800 rounded-xl">
                            <flux:radio value="1" label="1" />
                            <flux:radio value="2" label="2" />
                            <flux:radio value="3" label="3" />
                            <flux:radio value="4" label="4" />
                            <flux:radio value="5" label="5" />
                        </flux:radio.group>

                        <flux:field>
                            <flux:textarea wire:model="commentaire" placeholder="Un mot sur votre expérience (optionnel)..." rows="3" class="rounded-xl bg-zinc-50/50 dark:bg-zinc-800/50 border-none" />
                            <flux:error name="commentaire" />
                        </flux:field>

                        <flux:button type="submit" variant="primary" class="w-full rounded-xl py-3">
                            Envoyer mon avis
                        </flux:button>
                    </form>
                @endif
            </div>
        @endif
    @endif
</div>
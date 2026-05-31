<?php

use App\Models\Colis;
use App\Models\ColisHistory;
use App\Models\User;
use App\Enums\Role;
use App\Enums\ColisStatus;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Illuminate\Support\Carbon;

new #[Layout('components.layouts.app')] class extends Component {
    #[Url]
    public string $startDate = '';
    
    #[Url]
    public string $endDate = '';

    public function mount()
    {
        $this->startDate = $this->startDate ?: now()->subDays(30)->format('Y-m-d');
        $this->endDate = $this->endDate ?: now()->format('Y-m-d');
    }

    #[Computed]
    public function stats()
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        $delivered = Colis::where('statut', ColisStatus::Livre)->whereBetween('updated_at', [$start, $end])->count();
        $returned = Colis::where('statut', ColisStatus::Retourne)->whereBetween('updated_at', [$start, $end])->count();
        $totalSuccessFailure = $delivered + $returned;

        return [
            'colis_en_cours' => Colis::whereIn('statut', [ColisStatus::Enregistre, ColisStatus::Ramasse, ColisStatus::EnCours])->whereBetween('created_at', [$start, $end])->count(),
            'livreurs_actifs' => User::where('role', Role::Livreur)->count(),
            'revenus_frais' => Colis::where('statut', ColisStatus::Livre)->whereBetween('updated_at', [$start, $end])->sum('frais_livraison'),
            'total_fonds' => Colis::where('statut', ColisStatus::Livre)->whereBetween('updated_at', [$start, $end])->sum('prix_colis'),
            'total_colis' => Colis::whereBetween('created_at', [$start, $end])->count(),
            'taux_reussite' => $totalSuccessFailure > 0 ? round(($delivered / $totalSuccessFailure) * 100) : 0,
            'recent_colis' => Colis::with(['client'])->latest()->take(5)->get(),
        ];
    }

    #[Computed]
    public function chartData(): array
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        $dates = [];
        $current = $start->copy();
        while ($current <= $end) {
            $dates[$current->format('Y-m-d')] = ['created' => 0, 'delivered' => 0];
            $current->addDay();
        }

        $createdData = Colis::whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        $deliveredData = ColisHistory::where('statut', ColisStatus::Livre)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        foreach ($createdData as $date => $count) if (isset($dates[$date])) $dates[$date]['created'] = $count;
        foreach ($deliveredData as $date => $count) if (isset($dates[$date])) $dates[$date]['delivered'] = $count;

        return [
            'activity' => [
                'labels' => array_keys($dates),
                'created' => array_values(array_column($dates, 'created')),
                'delivered' => array_values(array_column($dates, 'delivered')),
            ],
            'distribution' => [
                'labels' => ['Enregistrés', 'Ramassés', 'En cours', 'Livrés', 'Retournés'],
                'data' => [
                    Colis::where('statut', ColisStatus::Enregistre)->whereBetween('created_at', [$start, $end])->count(),
                    Colis::where('statut', ColisStatus::Ramasse)->whereBetween('created_at', [$start, $end])->count(),
                    Colis::where('statut', ColisStatus::EnCours)->whereBetween('created_at', [$start, $end])->count(),
                    Colis::where('statut', ColisStatus::Livre)->whereBetween('created_at', [$start, $end])->count(),
                    Colis::where('statut', ColisStatus::Retourne)->whereBetween('created_at', [$start, $end])->count(),
                ]
            ]
        ];
    }

    #[Computed]
    public function leaderboards(): array
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        return [
            'clients' => Colis::whereBetween('created_at', [$start, $end])
                ->select('client_id')
                ->groupBy('client_id')
                ->selectRaw('client_id, count(*) as count')
                ->orderByDesc('count')
                ->take(5)
                ->with('client')
                ->get(),
            'livreurs' => Colis::where('statut', ColisStatus::Livre)
                ->whereBetween('updated_at', [$start, $end])
                ->select('livreur_id')
                ->groupBy('livreur_id')
                ->selectRaw('livreur_id, count(*) as count')
                ->orderByDesc('count')
                ->take(5)
                ->with('livreur')
                ->get(),
        ];
    }
}; ?>

<div class="animate-fade-in pb-12">
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">Analyse de Performance</flux:heading>
            <flux:subheading>Visualisez l'activité logistique sur une période donnée.</flux:subheading>
        </div>

        <div class="flex items-center gap-2 bg-white dark:bg-zinc-900 p-2 rounded-xl border border-zinc-200 dark:border-zinc-800">
            <input type="date" wire:model.live="startDate" class="bg-transparent border-none text-sm focus:ring-0 text-zinc-600 dark:text-zinc-400">
            <span class="text-zinc-400">→</span>
            <input type="date" wire:model.live="endDate" class="bg-transparent border-none text-sm focus:ring-0 text-zinc-600 dark:text-zinc-400">
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
        <flux:card class="bg-indigo-50 dark:bg-indigo-950/20 border-none shadow-sm p-4">
            <flux:heading size="sm" class="text-indigo-700 dark:text-indigo-300 italic">Volume Période</flux:heading>
            <div class="mt-2 text-3xl font-bold tracking-tight text-indigo-600 dark:text-indigo-400">{{ $this->stats['total_colis'] }}</div>
        </flux:card>

        <flux:card class="bg-emerald-50 dark:bg-emerald-950/20 border-none shadow-sm p-4">
            <flux:heading size="sm" class="text-emerald-700 dark:text-emerald-300">Paiements (DH)</flux:heading>
            <div class="mt-2 text-2xl font-bold tracking-tight text-emerald-600 dark:text-emerald-400">{{ number_format($this->stats['total_fonds'], 0) }}</div>
        </flux:card>

        <flux:card class="bg-amber-50 dark:bg-amber-950/20 border-none shadow-sm p-4 text-center">
            <flux:heading size="sm" class="text-amber-700 dark:text-amber-300">Taux Réussite</flux:heading>
            <div class="mt-2 text-3xl font-bold tracking-tight text-amber-600 dark:text-amber-400">{{ $this->stats['taux_reussite'] }}%</div>
        </flux:card>

        <flux:card class="bg-zinc-50 dark:bg-zinc-900 border-none shadow-sm p-4">
            <flux:heading size="sm" class="text-zinc-500">Revenue (CA)</flux:heading>
            <div class="mt-2 text-2xl font-bold tracking-tight text-zinc-900 dark:white">{{ number_format($this->stats['revenus_frais'], 0) }} DH</div>
        </flux:card>

        <flux:card class="bg-zinc-50 dark:bg-zinc-900 border-none shadow-sm p-4">
            <flux:heading size="sm" class="text-zinc-500">Colis Actifs</flux:heading>
            <div class="mt-2 text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">{{ $this->stats['colis_en_cours'] }}</div>
        </flux:card>

        <flux:card class="bg-blue-50 dark:bg-blue-950/20 border-none shadow-sm p-4">
            <flux:heading size="sm" class="text-blue-700 dark:text-blue-300">Livreurs</flux:heading>
            <div class="mt-2 text-3xl font-bold tracking-tight text-blue-600 dark:text-blue-400">{{ $this->stats['livreurs_actifs'] }}</div>
        </flux:card>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12" 
        x-data="{
            activityChart: null,
            statusDonut: null,
            init() {
                this.renderCharts();
                document.addEventListener('livewire:navigated', () => this.renderCharts(), { once: true });
            },
            renderCharts() {
                const data = @js($this->chartData);
                const isDark = document.documentElement.classList.contains('dark');
                
                if (! document.querySelector('#activity-chart')) return;

                // Activity Chart
                if (this.activityChart) this.activityChart.destroy();
                this.activityChart = new ApexCharts(document.querySelector('#activity-chart'), {
                    series: [
                        { name: 'Nouveaux Colis', data: data.activity.created },
                        { name: 'Colis Livrés', data: data.activity.delivered }
                    ],
                    chart: { type: 'area', height: 320, toolbar: { show: false }, zoom: { enabled: false }, fontFamily: 'Instrument Sans, sans-serif', background: 'transparent' },
                    theme: { mode: isDark ? 'dark' : 'light' },
                    colors: ['#6366f1', '#10b981'],
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth', width: 2 },
                    xaxis: { categories: data.activity.labels, axisBorder: { show: false }, axisTicks: { show: false } },
                    yaxis: { labels: { formatter: val => Math.floor(val) } },
                    grid: { borderColor: isDark ? '#27272a' : '#f1f1f1' },
                    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1, stops: [0, 90, 100] } },
                    legend: { position: 'top', horizontalAlign: 'right' }
                });
                this.activityChart.render();

                // Status Donut
                if (this.statusDonut) this.statusDonut.destroy();
                this.statusDonut = new ApexCharts(document.querySelector('#status-donut'), {
                    series: data.distribution.data,
                    chart: { type: 'donut', height: 320, fontFamily: 'Instrument Sans, sans-serif', background: 'transparent' },
                    theme: { mode: isDark ? 'dark' : 'light' },
                    labels: data.distribution.labels,
                    colors: ['#71717a', '#3b82f6', '#f59e0b', '#10b981', '#ef4444'],
                    legend: { position: 'bottom' },
                    dataLabels: { enabled: false },
                    plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, label: 'TOTAL', color: isDark ? '#a1a1aa' : '#71717a' } } } } }
                });
                this.statusDonut.render();
            }
        }"
        x-on:chart-updated.window="renderCharts()"
    >
        <flux:card class="lg:col-span-2 p-6 border-none shadow-sm">
            <flux:heading size="lg" class="mb-6">Activité Quotidienne (Nouveaux vs Livrés)</flux:heading>
            <div id="activity-chart" wire:ignore class="w-full h-80"></div>
        </flux:card>

        <flux:card class="p-6 border-none shadow-sm">
            <flux:heading size="lg" class="mb-6">Répartition par Statut</flux:heading>
            <div id="status-donut" wire:ignore class="w-full h-80"></div>
        </flux:card>
    </div>

    {{-- Leaderboards Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <flux:card class="p-6 border-none shadow-sm">
            <flux:heading size="lg" class="mb-6">Top Clients (Volume)</flux:heading>
            <div class="space-y-4">
                @foreach($this->leaderboards['clients'] as $clientData)
                    <div class="flex items-center gap-4">
                        <flux:avatar size="sm" :name="$clientData->client?->name ?? 'Anonyme'" />
                        <div class="flex-1">
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium">{{ $clientData->client?->name ?? 'Client Supprimé' }}</span>
                                <span class="text-sm text-zinc-500">{{ $clientData->count }} colis</span>
                            </div>
                            <div class="w-full bg-zinc-100 dark:bg-zinc-800 rounded-full h-1.5">
                                <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {{ min(100, ($clientData->count / max(1, $this->leaderboards['clients'][0]->count)) * 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </flux:card>

        <flux:card class="p-6 border-none shadow-sm">
            <flux:heading size="lg" class="mb-6">Top Livreurs (Livraisons Réussies)</flux:heading>
            <div class="space-y-4">
                @foreach($this->leaderboards['livreurs'] as $livData)
                    <div class="flex items-center gap-4">
                        <flux:avatar size="sm" :name="$livData->livreur?->name ?? 'Livreur'" />
                        <div class="flex-1">
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium">{{ $livData->livreur?->name ?? 'Livreur Inconnu' }}</span>
                                <span class="text-sm text-emerald-500 font-bold">{{ $livData->count }} livrés</span>
                            </div>
                            <div class="w-full bg-zinc-100 dark:bg-zinc-800 rounded-full h-1.5">
                                <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ min(100, ($livData->count / max(1, $this->leaderboards['livreurs'][0]->count)) * 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </flux:card>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@endpush

<?php

use App\Models\Colis;
use App\Enums\ColisStatus;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

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
    public function stats(): array
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();
        $client = Auth::user();

        $delivered = $client->colisAsClient()->where('statut', ColisStatus::Livre)->whereBetween('updated_at', [$start, $end]);

        return [
            'total_colis' => $client->colisAsClient()->whereBetween('created_at', [$start, $end])->count(),
            'a_recuperer' => (float)$delivered->sum('prix_colis'),
            'frais_payes' => (float)$delivered->sum('frais_livraison'),
            'en_cours' => $client->colisAsClient()->whereIn('statut', [ColisStatus::Enregistre, ColisStatus::Ramasse, ColisStatus::EnCours])->whereBetween('created_at', [$start, $end])->count(),
            'livres' => $delivered->count(),
            'retournes' => $client->colisAsClient()->where('statut', ColisStatus::Retourne)->whereBetween('updated_at', [$start, $end])->count(),
        ];
    }

    #[Computed]
    public function chartData(): array
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();
        $client = Auth::user();

        $dates = [];
        $current = $start->copy();
        while ($current <= $end) {
            $dates[$current->format('Y-m-d')] = 0;
            $current->addDay();
        }

        $createdData = $client->colisAsClient()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        foreach ($createdData as $date => $count) if (isset($dates[$date])) $dates[$date] = $count;

        return [
            'activity' => [
                'labels' => array_keys($dates),
                'data' => array_values($dates),
            ],
            'distribution' => [
                'labels' => ['En cours', 'Livrés', 'Retournés'],
                'data' => [
                    (int)$this->stats['en_cours'],
                    (int)$this->stats['livres'],
                    (int)$this->stats['retournes'],
                ]
            ]
        ];
    }
}; ?>

<div class="animate-fade-in pb-12">
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">Tableau de bord Client</flux:heading>
            <flux:subheading>Suivez vos expéditions et vos revenus sur la période.</flux:subheading>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 bg-white dark:bg-zinc-900 p-2 rounded-xl border border-zinc-200 dark:border-zinc-800">
                <input type="date" wire:model.live="startDate" class="bg-transparent border-none text-sm focus:ring-0 text-zinc-600 dark:text-zinc-400">
                <span class="text-zinc-400">→</span>
                <input type="date" wire:model.live="endDate" class="bg-transparent border-none text-sm focus:ring-0 text-zinc-600 dark:text-zinc-400">
            </div>

            <div class="flex items-center gap-2">
                <flux:button href="{{ route('client.colis') }}" variant="ghost" icon="archive-box" wire:navigate>Mes colis</flux:button>
                <flux:button href="{{ route('client.colis.create') }}" variant="primary" icon="plus" wire:navigate>Nouveau colis</flux:button>
            </div>
        </div>
    </div>

    {{-- Bilan Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
        <flux:card class="p-4 border-none shadow-sm bg-zinc-50 dark:bg-zinc-900">
            <flux:heading size="sm" class="text-zinc-500">Expéditions</flux:heading>
            <div class="mt-1 text-2xl font-bold tracking-tight">{{ $this->stats['total_colis'] }}</div>
        </flux:card>

        <flux:card class="p-4 border-none shadow-sm bg-emerald-50 dark:bg-emerald-950/20">
            <flux:heading size="sm" class="text-emerald-700 dark:text-emerald-300">Net à recevoir</flux:heading>
            <div class="mt-1 text-xl font-bold tracking-tight text-emerald-600 dark:text-emerald-400">{{ number_format($this->stats['a_recuperer'], 2) }} DH</div>
        </flux:card>

        <flux:card class="p-4 border-none shadow-sm bg-indigo-50 dark:bg-indigo-950/20">
            <flux:heading size="sm" class="text-indigo-700 dark:text-indigo-300">Frais payés</flux:heading>
            <div class="mt-1 text-xl font-bold tracking-tight text-indigo-600 dark:text-indigo-400">{{ number_format($this->stats['frais_payes'], 2) }} DH</div>
        </flux:card>

        <flux:card class="p-4 border-none shadow-sm">
            <flux:heading size="sm" class="text-amber-600">En cours</flux:heading>
            <div class="mt-1 text-2xl font-bold tracking-tight">{{ $this->stats['en_cours'] }}</div>
        </flux:card>

        <flux:card class="p-4 border-none shadow-sm">
            <flux:heading size="sm" class="text-emerald-600">Livrés</flux:heading>
            <div class="mt-1 text-2xl font-bold tracking-tight">{{ $this->stats['livres'] }}</div>
        </flux:card>

        <flux:card class="p-4 border-none shadow-sm">
            <flux:heading size="sm" class="text-red-500">Retournés</flux:heading>
            <div class="mt-1 text-2xl font-bold tracking-tight text-red-500">{{ $this->stats['retournes'] }}</div>
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
                    series: [{ name: 'Colis', data: data.activity.data }],
                    chart: { type: 'area', height: 320, toolbar: { show: false }, zoom: { enabled: false }, fontFamily: 'Instrument Sans, sans-serif', background: 'transparent' },
                    theme: { mode: isDark ? 'dark' : 'light' },
                    colors: ['#6366f1'],
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth', width: 2 },
                    xaxis: { categories: data.activity.labels, axisBorder: { show: false }, axisTicks: { show: false } },
                    yaxis: { labels: { formatter: val => Math.floor(val) } },
                    grid: { borderColor: isDark ? '#27272a' : '#f1f1f1' },
                    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1, stops: [0, 90, 100] } }
                });
                this.activityChart.render();

                // Status Donut
                if (this.statusDonut) this.statusDonut.destroy();
                this.statusDonut = new ApexCharts(document.querySelector('#status-donut'), {
                    series: data.distribution.data,
                    chart: { type: 'donut', height: 320, fontFamily: 'Instrument Sans, sans-serif', background: 'transparent' },
                    theme: { mode: isDark ? 'dark' : 'light' },
                    labels: data.distribution.labels,
                    colors: ['#f59e0b', '#10b981', '#ef4444'],
                    legend: { position: 'bottom' },
                    dataLabels: { enabled: false },
                    plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, label: 'TOTAL', color: isDark ? '#a1a1aa' : '#71717a' } } } } }
                });
                this.statusDonut.render();
            }
        }"
    >
        <flux:card class="lg:col-span-2 p-6 border-none shadow-sm">
            <flux:heading size="lg" class="mb-6">Mes Expéditions par Jour</flux:heading>
            <div id="activity-chart" wire:ignore class="w-full h-80"></div>
        </flux:card>

        <flux:card class="p-6 border-none shadow-sm flex flex-col justify-center">
            <flux:heading size="lg" class="mb-6">Répartition par Statut</flux:heading>
            <div id="status-donut" wire:ignore class="w-full h-80"></div>
        </flux:card>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@endpush
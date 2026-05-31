<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Shipily - Gestion de Livraison Simplifiée</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance
    </head>
    <body class="min-h-screen bg-white text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
        <div class="relative overflow-hidden">
            <!-- Navigation -->
            <header class="mx-auto max-w-7xl px-6 py-8 flex items-center justify-between relative z-10">
                <div class="flex items-center gap-2">
                    <x-app-logo />
                </div>

                <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                    <a href="#features" class="hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">Fonctionnalités</a>
                    <a href="{{ route('tracking') }}" class="hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">Suivi Colis</a>
                </nav>

                <div class="flex items-center gap-4">
                    @auth
                        <flux:button href="{{ url('/dashboard') }}" variant="primary" size="sm" wire:navigate>
                            Tableau de bord
                        </flux:button>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors">Connexion</a>
                        <flux:button href="{{ route('register') }}" variant="primary" size="sm" wire:navigate>
                            Commencer
                        </flux:button>
                    @endauth
                </div>
            </header>

            <!-- Hero Section -->
            <main class="mx-auto max-w-7xl px-6 pt-16 pb-24 md:pt-32 md:pb-40 relative z-10 text-center">
                <div class="inline-flex items-center rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200 mb-8 animate-fade-in">
                    <span class="mr-2 flex h-2 w-2 items-center justify-center rounded-full bg-green-500">
                        <span class="h-2 w-2 animate-ping rounded-full bg-green-400 opacity-75"></span>
                    </span>
                    Nouveau : Tracking GPS en temps réel disponible
                </div>

                <h1 class="mx-auto max-w-4xl text-5xl font-bold tracking-tight text-zinc-900 md:text-7xl dark:text-white leading-[1.1]">
                    La gestion de vos livraisons, <span class="text-zinc-400 dark:text-zinc-500">en toute simplicité.</span>
                </h1>

                <p class="mx-auto mt-8 max-w-2xl text-lg text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    Shipily est la plateforme centralisée pour les agences logistiques et les e-commerçants. Suivez vos colis, gérez vos livreurs et optimisez vos flux de livraison.
                </p>

                <div class="mt-12 flex flex-col items-center gap-6">
                    <form action="{{ route('tracking') }}" method="GET" class="w-full max-w-md flex gap-2">
                        <flux:input name="codeSuivi" placeholder="Entrez votre code de suivi (ex: TRK...)" class="flex-1" icon="magnifying-glass" />
                        <flux:button type="submit" variant="primary">Suivre</flux:button>
                    </form>
                    <p class="text-xs text-zinc-400 dark:text-zinc-500 italic">
                        Accédez au suivi public sans compte utilisateur.
                    </p>
                </div>
            </main>

            <!-- Gradient background effect -->
            <div class="absolute top-0 left-1/2 -z-10 h-[600px] w-full -translate-x-1/2 [mask-image:radial-gradient(closest-side,white,transparent)] sm:h-[800px]" aria-hidden="true">
                <div class="absolute inset-0 bg-gradient-to-tr from-[#fb7185] to-[#f472b6] opacity-10 dark:opacity-20"></div>
                <div class="absolute inset-0 bg-gradient-to-bl from-[#6366f1] to-[#a855f7] opacity-10 dark:opacity-20 translate-x-1/2"></div>
            </div>
        </div>

        <!-- Features Section -->
        <section id="features" class="py-24 bg-zinc-50 dark:bg-zinc-900/50 border-t border-zinc-200 dark:border-zinc-800">
            <div class="mx-auto max-w-7xl px-6">
                <div class="grid grid-cols-1 gap-12 md:grid-cols-3">
                    <div class="space-y-4">
                        <div class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-900 text-white dark:bg-white dark:text-zinc-900">
                            <flux:icon.truck class="size-6" />
                        </div>
                        <h3 class="text-lg font-bold text-zinc-900 dark:text-white">Gestion E-commerce</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                            Ajoutez vos colis en quelques clics et suivez leur progression en temps réel jusqu'au destinataire final.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <div class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-900 text-white dark:bg-white dark:text-zinc-900">
                            <flux:icon.map-pin class="size-6" />
                        </div>
                        <h3 class="text-lg font-bold text-zinc-900 dark:text-white">Tracking GPS</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                            Suivez la position exacte de vos livreurs pour une transparence totale et une meilleure estimation des délais.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <div class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-900 text-white dark:bg-white dark:text-zinc-900">
                            <flux:icon.chart-bar class="size-6" />
                        </div>
                        <h3 class="text-lg font-bold text-zinc-900 dark:text-white">Analyse Admin</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                            Tableaux de bord complets pour assigner les colis, gérer les livreurs et analyser les performances de votre agence.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-zinc-200 py-12 dark:border-zinc-800">
            <div class="mx-auto max-w-7xl px-6 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-2">
                    <x-app-logo />
                </div>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    &copy; {{ date('Y') }} Shipily Logistique. Projet de Fin d'Études.
                </p>
                <div class="flex gap-6">
                    <a href="#" class="text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">
                        <flux:icon.globe-alt class="size-5" />
                    </a>
                </div>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>

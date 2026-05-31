<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-950 antialiased">
        <flux:sidebar sticky stashable class="border-r border-zinc-200 bg-zinc-50/50 backdrop-blur-sm dark:border-zinc-800 dark:bg-zinc-900/50">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <div class="px-2 mb-6 mt-4">
                <a href="/" class="flex items-center space-x-2" wire:navigate>
                    <x-app-logo />
                </a>
            </div>

            <flux:navlist variant="outline" class="space-y-1">
                @if(auth()->user()->isAdmin())
                    <flux:navlist.item icon="home" href="{{ route('admin.dashboard') }}" :current="request()->routeIs('admin.dashboard')" wire:navigate>Tableau de bord</flux:navlist.item>
                    <flux:navlist.item icon="archive-box" href="{{ route('admin.colis') }}" :current="request()->routeIs('admin.colis')" wire:navigate>Gérer les colis</flux:navlist.item>
                    <flux:navlist.item icon="users" href="{{ route('admin.users') }}" :current="request()->routeIs('admin.users')" wire:navigate>Utilisateurs</flux:navlist.item>
                @elseif(auth()->user()->isClient())
                    <flux:navlist.item icon="home" href="{{ route('client.dashboard') }}" :current="request()->routeIs('client.dashboard')" wire:navigate>Tableau de bord</flux:navlist.item>
                    <flux:navlist.item icon="archive-box" href="{{ route('client.colis') }}" :current="request()->routeIs('client.colis')" wire:navigate>Mes colis</flux:navlist.item>
                    <flux:navlist.item icon="plus-circle" href="{{ route('client.colis.create') }}" :current="request()->routeIs('client.colis.create')" wire:navigate>Nouveau colis</flux:navlist.item>
                @elseif(auth()->user()->isLivreur())
                    <div class="px-2 mb-4">
                        <livewire:livreur.location-tracker />
                    </div>
                    <flux:navlist.item icon="home" href="{{ route('livreur.dashboard') }}" :current="request()->routeIs('livreur.dashboard')" wire:navigate>Tableau de bord</flux:navlist.item>
                    <flux:navlist.item icon="truck" href="{{ route('livreur.colis') }}" :current="request()->routeIs('livreur.colis')" wire:navigate>Mes livraisons</flux:navlist.item>
                @endif
            </flux:navlist>

            <flux:spacer />

            <!-- Desktop User Menu -->
            <flux:dropdown position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevrons-up-down"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-left text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item href="/settings/profile" icon="cog" wire:navigate>Paramètres</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            Déconnexion
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-left text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item href="/settings/profile" icon="cog" wire:navigate>Paramètres</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            Déconnexion
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @stack('scripts')
        @fluxScripts
    </body>
</html>

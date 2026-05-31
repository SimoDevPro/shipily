<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $role = 'client';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'phone' => ['required', 'string', 'max:20'],
            'role' => ['required', 'string', 'in:client,livreur'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        
        // Map string to Enum value
        $validated['role'] = $validated['role'] === 'livreur' ? \App\Enums\Role::Livreur : \App\Enums\Role::Client;

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header title="Créer un compte" description="Renseignez vos informations ci-dessous pour créer votre compte" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="register" class="flex flex-col gap-6">
        <!-- Name -->
        <div class="grid gap-2">
            <flux:input wire:model="name" id="name" label="Nom complet" type="text" name="name" required autofocus autocomplete="name" placeholder="John Doe" />
        </div>

        <!-- Email Address -->
        <div class="grid gap-2">
            <flux:input wire:model="email" id="email" label="Adresse e-mail" type="email" name="email" required autocomplete="email" placeholder="email@exemple.com" />
        </div>
        
        <!-- Phone Number -->
        <div class="grid gap-2">
            <flux:input wire:model="phone" id="phone" label="Numéro de téléphone" type="tel" name="phone" required autocomplete="tel" placeholder="06XXXXXXXX" />
        </div>
        
        <!-- Role Selection -->
        <div class="grid gap-2">
            <flux:radio.group wire:model="role" label="Je suis un :" class="flex flex-col gap-2">
                <flux:radio value="client" label="Client (E-commerçant)" />
                <flux:radio value="livreur" label="Livreur (Chauffeur)" />
            </flux:radio.group>
        </div>

        <!-- Password -->
        <div class="grid gap-2">
            <flux:input
                wire:model="password"
                id="password"
                label="Mot de passe"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                placeholder="Mot de passe"
            />
        </div>

        <!-- Confirm Password -->
        <div class="grid gap-2">
            <flux:input
                wire:model="password_confirmation"
                id="password_confirmation"
                label="Confirmer le mot de passe"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="Confirmez le mot de passe"
            />
        </div>

        <div class="flex items-center justify-end">
            <flux:button type="submit" variant="primary" class="w-full">
                Créer le compte
            </flux:button>
        </div>
    </form>

    <div class="space-x-1 text-center text-sm text-zinc-600 dark:text-zinc-400">
        Vous avez déjà un compte ?
        <x-text-link href="{{ route('login') }}">Se connecter</x-text-link>
    </div>
</div>

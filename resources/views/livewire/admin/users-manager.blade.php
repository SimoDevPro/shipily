<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Enums\Role;
use Livewire\Attributes\Url;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public ?User $editingUser = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $phone = '';
    public string $role = 'client';

    public function with(): array
    {
        return [
            'users' => User::whereIn('role', [Role::Client, Role::Livreur])
                ->withAvg('avisRecus', 'note')
                ->latest()
                ->paginate(10),
        ];
    }

    public function edit(User $user): void
    {
        $this->editingUser = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->role = $user->role->value;
        $this->password = ''; 
        
        $this->modal('user-modal')->show();
    }

    public function save(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . ($this->editingUser?->id ?? 'NULL')],
            'phone' => ['required', 'string', 'max:20'],
            'role' => ['required', 'string', 'in:client,livreur'],
        ];

        if (!$this->editingUser) {
            $rules['password'] = ['required', 'string', 'min:8'];
        } else {
            $rules['password'] = ['nullable', 'string', 'min:8'];
        }

        $validated = $this->validate($rules);

        if ($this->editingUser) {
            $data = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'role' => Role::from($validated['role']),
            ];
            if (!empty($validated['password'])) {
                $data['password'] = Hash::make($validated['password']);
            }
            $this->editingUser->update($data);
            Flux::toast('Utilisateur mis à jour avec succès.');
        } else {
            User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'],
                'role' => Role::from($validated['role']),
            ]);
            Flux::toast('Utilisateur ajouté avec succès.');
        }

        $this->cancel();
        $this->modal('user-modal')->close();
    }

    public function delete(User $user): void
    {
        $user->delete();
        Flux::toast('Utilisateur supprimé.');
    }

    public function cancel(): void
    {
        $this->reset(['name', 'email', 'password', 'phone', 'role', 'editingUser']);
    }
}; ?>

<div>
    <flux:header class="flex justify-between items-start">
        <div>
            <flux:heading size="xl" level="1">Gestion des Utilisateurs</flux:heading>
            <flux:subheading>Gérez les clients et les livreurs de la plateforme.</flux:subheading>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="modal('user-modal').show()">Nouvel Utilisateur</flux:button>
    </flux:header>

    <div class="mt-6">
        <flux:table :paginate="$users">
            <flux:table.columns>
                <flux:table.column>Nom</flux:table.column>
                <flux:table.column>Rôle</flux:table.column>
                <flux:table.column>Score</flux:table.column>
                <flux:table.column>Email</flux:table.column>
                <flux:table.column>Téléphone</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($users as $user)
                    <flux:table.row :key="$user->id">
                        <flux:table.cell>
                            <div class="flex items-center gap-3">
                                <flux:avatar size="sm" :name="$user->name" />
                                <span class="font-medium">{{ $user->name }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge :color="$user->role === App\Enums\Role::Livreur ? 'blue' : 'green'" size="sm" inset="top bottom">
                                {{ $user->role === App\Enums\Role::Livreur ? 'Livreur' : 'Client' }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if($user->role === App\Enums\Role::Livreur)
                                @if($user->avis_recus_avg_note)
                                    <div class="flex items-center gap-1">
                                        <span class="font-bold text-amber-500">{{ number_format($user->avis_recus_avg_note, 1) }}</span>
                                        <flux:icon.star variant="solid" class="size-3.5 text-amber-500" />
                                    </div>
                                @else
                                    <span class="text-zinc-400 text-xs">Aucun avis</span>
                                @endif
                            @else
                                <span class="text-zinc-300">-</span>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell class="text-zinc-500">{{ $user->email }}</flux:table.cell>
                        <flux:table.cell class="text-zinc-500">{{ $user->phone }}</flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:button icon="pencil-square" variant="ghost" size="sm" wire:click="edit({{ $user->id }})" />
                                <flux:button icon="trash" variant="ghost" size="sm" wire:click="delete({{ $user->id }})" wire:confirm="Êtes-vous sûr de vouloir supprimer cet utilisateur ?" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>

    <flux:modal name="user-modal" class="min-w-[22rem]">
        <form wire:submit="save">
            <div>
                <flux:heading size="lg">{{ $editingUser ? 'Modifier l\'Utilisateur' : 'Ajouter un Utilisateur' }}</flux:heading>
                <flux:subheading>{{ $editingUser ? 'Mettez à jour les informations du compte.' : 'Créez un nouveau compte client ou livreur.' }}</flux:subheading>
            </div>

            <div class="space-y-6 mt-6">
                <flux:input wire:model="name" label="Nom complet" placeholder="Jean Dupont" required />

                <flux:input wire:model="email" type="email" label="Adresse email" placeholder="jean@exemple.com" required />

                <flux:input wire:model="phone" type="tel" label="Numéro de téléphone" placeholder="06..." required />

                <flux:select wire:model="role" label="Rôle" required>
                    <flux:select.option value="client">Client (E-commerce)</flux:select.option>
                    <flux:select.option value="livreur">Livreur</flux:select.option>
                </flux:select>

                <flux:input wire:model="password" type="password" :label="$editingUser ? 'Nouveau mot de passe (optionnel)' : 'Mot de passe'" :required="!$editingUser" />
            </div>

            <div class="flex mt-6 gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost" wire:click="cancel">Annuler</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="primary">{{ $editingUser ? 'Mettre à jour' : 'Ajouter' }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
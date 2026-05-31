<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        /** @var User $user */
        $user = auth()->user();

        return match ($user->role) {
            Role::Admin => redirect()->route('admin.dashboard'),
            Role::Client => redirect()->route('client.dashboard'),
            Role::Livreur => redirect()->route('livreur.dashboard'),
            default => abort(403),
        };
    })->name('dashboard');

    Route::middleware('role:admin')->group(function () {
        Volt::route('/admin', 'admin.dashboard')->name('admin.dashboard');
        Volt::route('/admin/colis', 'admin.colis-manager')->name('admin.colis');
        Volt::route('/admin/users', 'admin.users-manager')->name('admin.users');
    });
    
    Route::middleware('role:client')->group(function () {
        Volt::route('/client', 'client.dashboard')->name('client.dashboard');
        Volt::route('/client/colis', 'client.colis-manager')->name('client.colis');
        Volt::route('/client/colis/create', 'client.colis-create')->name('client.colis.create');
    });
    
    Route::middleware('role:livreur')->group(function () {
        Volt::route('/livreur', 'livreur.dashboard')->name('livreur.dashboard');
        Volt::route('/livreur/colis', 'livreur.colis-active')->name('livreur.colis');
    });
});

Volt::route('/track/{codeSuivi?}', 'public.tracking')->name('tracking');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';

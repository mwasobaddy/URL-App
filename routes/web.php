<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::view('/', 'welcome')->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
    
    // Notifications
    Volt::route('notifications', 'notifications')->name('notifications');
});

// URL-App: User dashboard and list management
Route::middleware(['auth'])->group(function () {
    Volt::route('lists/create', 'url-list-create')->name('lists.create');
    Volt::route('lists', 'url-list-dashboard')->name('lists.dashboard');
    Volt::route('lists/{custom_url}/manage', 'url-list-display')->name('lists.show');
    Volt::route('lists/{custom_url}/share', 'url-list-share')->name('lists.share');
    Route::get('lists/{urlList}/access', App\Livewire\ManageListAccess::class)->name('lists.access');
});

// Public route for viewing a published list
Volt::route('lists/{custom_url}', 'url-list-display')->name('lists.public');

require __DIR__.'/auth.php';

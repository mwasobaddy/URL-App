<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Livewire\Plans\PricingTable;
use App\Livewire\Plans\Subscribe;
use App\Livewire\Plans\ManageSubscription;
use App\Http\Controllers\SubscriptionController;

Route::view('/', 'welcome')->name('home');



Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth'])->group(function () {
    // User Profile
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
    
    // Notifications
    Volt::route('notifications', 'notifications')->name('notifications');

    // Subscription Routes
    Volt::route('/plans', 'plans.pricing-table')->name('plans');
    Volt::route('/subscribe/{plan}/{interval?}', 'plans.subscribe')->name('subscribe');
    Volt::route('/subscription', 'plans.manage-subscription')->name('subscription.manage');
    Route::get('/subscription/return', [SubscriptionController::class, 'return'])->name('subscription.return');
    Route::get('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    Route::post('/subscription/webhook', [SubscriptionController::class, 'webhook'])->name('subscription.webhook');
});

// URL-App: User dashboard and list management
Route::middleware(['auth'])->group(function () {
    Volt::route('lists/create', 'url-list-create')->name('lists.create');
    Volt::route('lists', 'url-list-dashboard')->name('lists.dashboard');
    Volt::route('lists/{custom_url}/manage', 'url-list-display')->name('lists.show');
    Volt::route('lists/{custom_url}/share', 'url-list-share')->name('lists.share');
    Volt::route('lists/{urlList}/access', 'manage-list-access')->name('lists.access');
});

// Public route for viewing a published list
Volt::route('lists/{custom_url}', 'url-list-display')->name('lists.public');

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Volt::route('/dashboard', 'admin.dashboard')->name('dashboard');
    
    // Subscriptions
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Volt::route('/', 'admin.subscriptions.index')->name('index');
        Volt::route('/customers', 'admin.customers.subscription-overview')->name('customers');
        Volt::route('/metrics', 'admin.subscriptions.metrics-dashboard')->name('metrics');
        Volt::route('/{subscription}', 'admin.subscriptions.show')->name('show');
    });
    
    // Users & Roles
    Route::prefix('users')->name('users.')->group(function () {
        Volt::route('/', 'admin.users.index')->name('index');
        Volt::route('/{user}', 'admin.users.show')->name('show');
        Volt::route('/roles', 'admin.users.roles')->name('roles');
    });
    
    // Plans
    Route::prefix('plans')->name('plans.')->group(function () {
        Volt::route('/', 'admin.plans.index')->name('index');
        Volt::route('/create', 'admin.plans.create')->name('create');
        Volt::route('/{plan}', 'admin.plans.show')->name('show');
        Volt::route('/{plan}/edit', 'admin.plans.edit')->name('edit');
    });
    
    // Plans
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Volt::route('/', 'admin.plans.index')->name('index');
        Volt::route('/create', 'admin.plans.create')->name('create');
        Volt::route('/{plan}', 'admin.plans.show')->name('show');
        Volt::route('/{plan}/edit', 'admin.plans.edit')->name('edit');
    });
    
    // Analytics
    Volt::route('/analytics', 'admin.analytics')->name('analytics');
    
    // Revenue
    Route::prefix('revenue')->name('revenue.')->group(function () {
        Volt::route('/', 'admin.revenue.analytics-dashboard')->name('analytics');
        Volt::route('/export', 'admin.revenue.export-reports')->name('export');
    });

    // System Monitoring
    Route::prefix('monitoring')->name('monitoring.')->group(function () {
        Volt::route('/health', 'admin.monitoring.health-dashboard')->name('health');
        Volt::route('/system-logs', 'admin.monitoring.system-logs')->name('system-logs');
    });
    
    // PayPal Webhooks
    Route::prefix('webhooks')->name('webhooks.')->group(function () {
        Volt::route('/', 'admin.webhooks.index')->name('index');
        Volt::route('/logs', 'admin.webhooks.logs')->name('logs');
    });
});

require __DIR__.'/auth.php';

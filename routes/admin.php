<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', \App\Livewire\Admin\Dashboard::class)->name('dashboard');
    
    // Subscriptions
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/', \App\Livewire\Admin\Subscriptions\Index::class)->name('index');
        Route::get('/customers', \App\Livewire\Admin\Customers\SubscriptionOverview::class)->name('customers');
        Route::get('/metrics', \App\Livewire\Admin\Subscriptions\MetricsDashboard::class)->name('metrics');
        Route::get('/{subscription}', \App\Livewire\Admin\Subscriptions\Show::class)->name('show');
    });
    
    // Users & Roles
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', \App\Livewire\Admin\Users\Index::class)->name('index');
        Route::get('/{user}', \App\Livewire\Admin\Users\Show::class)->name('show');
        Route::get('/roles', \App\Livewire\Admin\Users\Roles::class)->name('roles');
    });
    
    // Plans
    Route::prefix('plans')->name('plans.')->group(function () {
        Route::get('/', \App\Livewire\Admin\Plans\Index::class)->name('index');
        Route::get('/create', \App\Livewire\Admin\Plans\Create::class)->name('create');
        Route::get('/{plan}', \App\Livewire\Admin\Plans\Show::class)->name('show');
        Route::get('/{plan}/edit', \App\Livewire\Admin\Plans\Edit::class)->name('edit');
    });
    
    // Analytics
    Route::get('/analytics', \App\Livewire\Admin\Analytics::class)->name('analytics');
    
    // System Logs
    Route::get('/logs', \App\Livewire\Admin\Logs::class)->name('logs');
    
    // System Health
    Route::prefix('health')->name('health.')->group(function () {
        Route::get('/', \App\Livewire\Admin\Health\Index::class)->name('index');
        Route::get('/queues', \App\Livewire\Admin\Health\Queues::class)->name('queues');
        Route::get('/cache', \App\Livewire\Admin\Health\Cache::class)->name('cache');
    });
    
    // PayPal Webhooks
    Route::prefix('webhooks')->name('webhooks.')->group(function () {
        Route::get('/', \App\Livewire\Admin\Webhooks\Index::class)->name('index');
        Route::get('/logs', \App\Livewire\Admin\Webhooks\Logs::class)->name('logs');
    });
});

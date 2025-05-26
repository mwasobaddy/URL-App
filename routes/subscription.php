<?php

use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    // Subscription routes
    Route::get('/subscription/dashboard', \App\Livewire\Subscription\Dashboard::class)
        ->name('subscription.dashboard');
    Route::get('/subscription/success', [SubscriptionController::class, 'success'])
        ->name('subscription.success');
    Route::get('/subscription/cancelled', [SubscriptionController::class, 'cancelled'])
        ->name('subscription.cancelled');
    Route::get('/subscription/resumed', [SubscriptionController::class, 'resumed'])
        ->name('subscription.resumed');
    Route::get('/subscription/activate/{subscription}', \App\Livewire\SubscriptionActivation::class)
        ->name('subscription.activate');
});

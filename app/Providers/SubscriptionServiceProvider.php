<?php

namespace App\Providers;

use App\Services\RoleCheckService;
use App\Services\SubscriptionService;
use App\Services\UsageTrackingService;
use Illuminate\Support\ServiceProvider;

class SubscriptionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register RoleCheckService
        $this->app->singleton(RoleCheckService::class, function ($app) {
            return new RoleCheckService();
        });

        // Register SubscriptionService
        $this->app->singleton(SubscriptionService::class, function ($app) {
            return new SubscriptionService(
                $app->make(RoleCheckService::class)
            );
        });

        // Register UsageTrackingService
        $this->app->singleton(UsageTrackingService::class, function ($app) {
            return new UsageTrackingService();
        });
    }

    public function boot(): void
    {
        // Make services available in Volt components
        \Livewire\Volt\Volt::provide('subscriptionService', fn() => app(SubscriptionService::class));
        \Livewire\Volt\Volt::provide('usageTrackingService', fn() => app(UsageTrackingService::class));
        \Livewire\Volt\Volt::provide('roleCheckService', fn() => app(RoleCheckService::class));
    }
}

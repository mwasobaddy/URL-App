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
        // We don't need to explicitly make these services available to Volt components
        // as they can be injected directly into the component mount() method
        // or accessed using app() helper function in Volt components
    }
}

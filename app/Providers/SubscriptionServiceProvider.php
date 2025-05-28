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

        // Register PayPalTokenService first
        $this->app->singleton(\App\Services\PayPalTokenService::class, function ($app) {
            return new \App\Services\PayPalTokenService();
        });
        
        // Register PayPalAPIService next
        $this->app->singleton(\App\Services\PayPalAPIService::class, function ($app) {
            return new \App\Services\PayPalAPIService($app->make(\App\Services\PayPalTokenService::class));
        });
        
        // Register SubscriptionService with PayPalSubscriptionService null to avoid circular dependency
        $this->app->singleton(SubscriptionService::class, function ($app) {
            return new SubscriptionService(
                $app->make(RoleCheckService::class),
                null // Pass null for now, we'll set it later
            );
        });
        
        // Register PayPalSubscriptionService
        $this->app->singleton(\App\Services\PayPalSubscriptionService::class, function ($app) {
            $paypalApi = $app->make(\App\Services\PayPalAPIService::class);
            $subscriptionService = $app->make(SubscriptionService::class);
            return new \App\Services\PayPalSubscriptionService($paypalApi, $subscriptionService);
        });
        
        // Resolve the circular reference by setting the PayPalSubscriptionService on the SubscriptionService
        $this->app->afterResolving(SubscriptionService::class, function ($service, $app) {
            $service->setPayPalSubscriptionService($app->make(\App\Services\PayPalSubscriptionService::class));
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

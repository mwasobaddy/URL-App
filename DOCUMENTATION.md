# URL-App Documentation

## Service Injection in Volt Components

As of version 1.2.8, we've updated how services are accessed in Volt components. The previous approach using `Volt::provide()` is not compatible with Livewire Volt 1.7.1.

### Known Issues Fixed

1. **Error "Call to undefined method Livewire\Volt\VoltManager::provide()"**
   - This error occurred because the `provide()` method doesn't exist in Livewire Volt 1.7.1
   - We've updated the `SubscriptionServiceProvider` to remove these invalid calls
   - Services are now accessed using dependency injection or the `app()` helper function

2. **Route Registration Error with Livewire Components**
   - We fixed an issue where direct use of Livewire components in routes was causing errors
   - The route `/lists/{urlList}/access` has been temporarily changed to use a closure function
   - This ensures that the application can boot correctly

### Accessing Services in Volt Components

There are two recommended ways to access services in Volt components:

#### 1. Dependency Injection in `mount()` Method

```php
<?php

use function Livewire\Volt\{state};
use App\Services\SubscriptionService;

state([
    'subscription' => null,
]);

$mount = function (SubscriptionService $subscriptionService) {
    $this->subscription = $subscriptionService->getCurrentSubscription();
};
?>

<div>
    <!-- Component markup -->
</div>
```

#### 2. Using the `app()` Helper

```php
<?php

use function Livewire\Volt\{state};

state([
    'subscription' => null,
]);

$mount = function () {
    $this->subscription = app(\App\Services\SubscriptionService::class)->getCurrentSubscription();
};
?>

<div>
    <!-- Component markup -->
</div>
```

### Available Services

The following services are registered in the `SubscriptionServiceProvider` and can be injected:

- `RoleCheckService` - For checking user roles and permissions
- `SubscriptionService` - For managing user subscriptions
- `UsageTrackingService` - For tracking feature usage

### Best Practices

- Always use dependency injection when possible as it makes your code more testable
- Use the `app()` helper for simplicity in smaller components
- Avoid accessing the services directly in the Blade template section; fetch the data in the mount method instead

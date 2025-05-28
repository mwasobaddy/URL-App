# URL-App Documentation

## Navigation Structure

As of version 1.2.10, we've updated the navigation system to use a unified approach, combining both user and admin navigation elements in a single sidebar component.

### Role-Based Navigation

The sidebar (`resources/views/components/layouts/app/sidebar.blade.php`) now includes:

1. **Main Navigation**: Basic navigation for all users
   - Dashboard (Admin Dashboard for admin users, User Dashboard for regular users)
   - My URL Lists (available to all users)

2. **Administration Section**: Only visible to users with the "admin" role
   - Subscriptions
   - Users & Roles
   - Plans
   - Analytics
   - Revenue Analytics

3. **System Health Section**: Only visible to users with the "admin" role
   - System Logs
   - Monitoring
   - Health Checks
   - PayPal Webhooks

4. **Settings**: Available to all users
   - Profile
   - Password
   - Appearance

5. **Actions**: Available to all users
   - Create New List

6. **Subscription**: Available to all users
   - My Subscription
   - View Plans

### Implementation Details

- Admin-only sections are wrapped in a conditional check: `@if(auth()->user()->hasRole('admin'))`
- All navigation links use consistent styling for visual coherence
- Active state is managed using `request()->routeIs()` checks
- Admin routes are prefixed with `admin.` and use consistent naming patterns

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

use Livewire\Volt\Component;
use App\Services\SubscriptionService;

new class extends Component {
    public $subscription = null;
    
    public function mount(SubscriptionService $subscriptionService)
    {
        $this->subscription = $subscriptionService->getCurrentSubscription();
    }
}
?>

<div>
    <!-- Component markup -->
</div>
```

#### 2. Using the `app()` Helper

```php
<?php

use Livewire\Volt\Component;

new class extends Component {
    public $subscription = null;
    
    public function mount()
    {
        $this->subscription = app(\App\Services\SubscriptionService::class)->getCurrentSubscription();
    }
}
?>

<div>
    <!-- Component markup -->
</div>
```

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

## Error Pages Design System

The application includes a comprehensive set of custom error pages with modern UI/UX design:

### Base Template
The error pages use a shared base template (`resources/views/errors/minimal.blade.php`) that provides:
- Modern blue gradient background
- Responsive split-screen layout (content on left, illustration on right)
- Enhanced typography using Poppins font
- Advanced animation effects (float, pulse, shimmer)
- Improved button designs with interactive effects

### Custom Error Pages
Each error type has a dedicated page with custom SVG illustrations:

| Error Code | Description | Visual Theme |
|------------|-------------|--------------|
| 404 | Not Found | Person on scooter looking for connection |
| 403 | Forbidden | Shield and lock security visualization |
| 500 | Server Error | Server rack with error indicators |
| 503 | Service Unavailable | Maintenance worker with tools |
| 419 | Page Expired | Key/token visualization with timeout |
| 429 | Too Many Requests | Traffic light with queue visualization |
| 401 | Unauthorized | Padlock with login form visualization |
| 402 | Payment Required | Credit card and payment terminal |

### Design Features
- **Responsive Design**: All error pages are fully responsive
- **Interactive Elements**: Subtle animations and hover effects
- **Clear Messaging**: Enhanced error descriptions
- **Consistent Styling**: Design language matches the dashboard theme
- **Accessible**: High contrast text and clear visual indicators

### Implementation
Error pages extend the base template and provide:
- Custom error code
- Custom error message
- Detailed explanation
- SVG illustration specific to the error type


<?php

use function Livewire\Volt\{state, computed, mount};
use App\Models\User;
use App\Services\SubscriptionService;
use App\Services\UsageTrackingService;

state([
    'search' => '',
    'filter' => '',
    'perPage' => 10,
    'sortField' => 'created_at',
    'sortDirection' => 'desc'
]);

$customers = computed(function () {
    return User::query()
        ->with(['subscription.plan', 'subscription.planVersion'])
        ->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            });
        })
        ->when($this->filter, function ($query) {
            if ($this->filter === 'subscribed') {
                $query->whereHas('subscription', function($q) {
                    $q->where('status', 'active');
                });
            } elseif ($this->filter === 'trial') {
                $query->whereHas('subscription', function($q) {
                    $q->whereNotNull('trial_ends_at')
                      ->where('trial_ends_at', '>', now());
                });
            } elseif ($this->filter === 'cancelled') {
                $query->whereHas('subscription', function($q) {
                    $q->whereNotNull('cancelled_at');
                });
            } elseif ($this->filter === 'expired') {
                $query->whereHas('subscription', function($q) {
                    $q->where('status', 'expired');
                });
            } elseif ($this->filter === 'no_subscription') {
                $query->doesntHave('subscription');
            }
        })
        ->orderBy($this->sortField, $this->sortDirection)
        ->paginate($this->perPage);
});

$stats = computed(function () {
    return [
        'total' => User::count(),
        'subscribed' => User::whereHas('subscription', function($q) {
            $q->where('status', 'active');
        })->count(),
        'trial' => User::whereHas('subscription', function($q) {
            $q->whereNotNull('trial_ends_at')
              ->where('trial_ends_at', '>', now());
        })->count(),
        'cancelled' => User::whereHas('subscription', function($q) {
            $q->whereNotNull('cancelled_at');
        })->count(),
    ];
});

$sort = function (string $field) {
    if ($this->sortField === $field) {
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        $this->sortField = $field;
        $this->sortDirection = 'asc';
    }
};

$getUsageStats = function (User $user) {
    $service = app(UsageTrackingService::class);
    return $service->getTotalUsage($user);
};

$getFeatureLimits = function (User $user) {
    $service = app(SubscriptionService::class);
    return $service->getFeatureLimits($user);
};

$getUsagePercentage = function ($used, $limit) {
    if ($limit === -1) return 0; // Unlimited
    if ($limit === 0) return 100; // No limit set
    return min(100, ($used / $limit) * 100);
};

?>

<div>
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                Customer Subscription Overview
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Monitor customer subscriptions and usage metrics
            </p>
        </div>
    </div>

    {{-- Stats Overview --}}
    <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <flux:stat-card
            title="Total Customers"
            :value="$this->stats['total']"
            icon="users"
            trend="none"
        />
        
        <flux:stat-card
            title="Subscribed"
            :value="$this->stats['subscribed']"
            icon="check-circle"
            type="success"
            trend="none"
        />
        
        <flux:stat-card
            title="On Trial"
            :value="$this->stats['trial']"
            icon="clock"
            type="info"
            trend="none"
        />
        
        <flux:stat-card
            title="Cancelled"
            :value="$this->stats['cancelled']"
            icon="x-circle"
            type="danger"
            trend="none"
        />
    </div>

    {{-- Filters --}}
    <div class="mt-6 bg-white dark:bg-zinc-800 shadow-sm rounded-lg divide-y dark:divide-zinc-700">
        <div class="px-4 py-5 sm:p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <flux:input
                    type="search"
                    wire:model.live="search"
                    placeholder="Search customers..."
                    icon="magnifying-glass"
                />

                <flux:select
                    wire:model.live="filter"
                    :options="[
                        '' => 'All Customers',
                        'subscribed' => 'Active Subscriptions',
                        'trial' => 'Trial Period',
                        'cancelled' => 'Cancelled',
                        'expired' => 'Expired',
                        'no_subscription' => 'No Subscription'
                    ]"
                />

                <flux:select
                    wire:model.live="perPage"
                    :options="[
                        10 => '10 per page',
                        25 => '25 per page',
                        50 => '50 per page',
                        100 => '100 per page'
                    ]"
                />
            </div>
        </div>

        {{-- Customers Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                <thead class="bg-gray-50 dark:bg-zinc-800/50">
                    <tr>
                        <th wire:click="sort('name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer">
                            Customer
                            @if($sortField === 'name')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Subscription
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Usage
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-800 divide-y divide-gray-200 dark:divide-zinc-700">
                    @foreach($this->customers as $customer)
                        @php
                            $usage = $getUsageStats($customer);
                            $limits = $getFeatureLimits($customer);
                        @endphp
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center">
                                            <span class="text-emerald-600 dark:text-emerald-400 font-medium text-sm">
                                                {{ strtoupper(substr($customer->name, 0, 2)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $customer->name }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $customer->email }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($customer->subscription)
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $customer->subscription->plan->name }}
                                        ({{ ucfirst($customer->subscription->interval) }})
                                    </div>
                                    <div class="mt-1">
                                        <x-subscription-status :status="$customer->subscription->status" />
                                    </div>
                                    @if($customer->subscription->trial_ends_at && $customer->subscription->trial_ends_at->isFuture())
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Trial ends {{ $customer->subscription->trial_ends_at->diffForHumans() }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-sm text-gray-500 dark:text-gray-400">No subscription</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-2">
                                    {{-- Lists Usage --}}
                                    <div>
                                        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                                            <span>Lists</span>
                                            <span>
                                                {{ $usage['lists'] ?? 0 }} /
                                                {{ $limits['lists'] === -1 ? '∞' : $limits['lists'] }}
                                            </span>
                                        </div>
                                        <div class="w-32 h-1 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                            <div 
                                                class="h-full bg-emerald-500 rounded-full" 
                                                style="width: {{ $getUsagePercentage($usage['lists'] ?? 0, $limits['lists']) }}%"
                                            ></div>
                                        </div>
                                    </div>

                                    {{-- URLs per List Usage --}}
                                    <div>
                                        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                                            <span>URLs/List</span>
                                            <span>
                                                {{ $usage['urls_per_list'] ?? 0 }} /
                                                {{ $limits['urls_per_list'] === -1 ? '∞' : $limits['urls_per_list'] }}
                                            </span>
                                        </div>
                                        <div class="w-32 h-1 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                            <div 
                                                class="h-full bg-emerald-500 rounded-full" 
                                                style="width: {{ $getUsagePercentage($usage['urls_per_list'] ?? 0, $limits['urls_per_list']) }}%"
                                            ></div>
                                        </div>
                                    </div>

                                    {{-- Collaborators Usage --}}
                                    <div>
                                        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                                            <span>Collaborators</span>
                                            <span>
                                                {{ $usage['collaborators'] ?? 0 }} /
                                                {{ $limits['collaborators'] === -1 ? '∞' : $limits['collaborators'] }}
                                            </span>
                                        </div>
                                        <div class="w-32 h-1 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                            <div 
                                                class="h-full bg-emerald-500 rounded-full" 
                                                style="width: {{ $getUsagePercentage($usage['collaborators'] ?? 0, $limits['collaborators']) }}%"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @if($customer->subscription)
                                    <a 
                                        href="{{ route('admin.subscriptions.show', $customer->subscription) }}" 
                                        class="text-emerald-600 hover:text-emerald-900 dark:text-emerald-400 dark:hover:text-emerald-300"
                                    >
                                        View Details
                                    </a>
                                @else
                                    <a 
                                        href="{{ route('admin.customers.show', $customer) }}" 
                                        class="text-emerald-600 hover:text-emerald-900 dark:text-emerald-400 dark:hover:text-emerald-300"
                                    >
                                        View Customer
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-4 py-3 bg-gray-50 dark:bg-zinc-800/50">
            {{ $this->customers->links() }}
        </div>
    </div>
</div>

<?php

use App\Models\User;
use App\Services\SubscriptionService;
use App\Services\UsageTrackingService;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Livewire\Volt\Component;

new class extends Component {
    use WithPagination;
    
    public $search = '';
    public $filter = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    
    #[Computed]
    public function customers()
    {
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
    }
    
    #[Computed]
    public function stats()
    {
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
    }
    
    public function sort(string $field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }
    
    public function getUsageStats(User $user)
    {
        try {
            $service = app(UsageTrackingService::class);
            return $service->getTotalUsage($user);
        } catch (\Exception $e) {
            \Log::error('Error getting usage stats: ' . $e->getMessage());
            return [
                'lists' => 0,
                'urls' => 0,
                'collaborators' => 0,
            ];
        }
    }
    
    public function getFeatureLimits(User $user)
    {
        try {
            $service = app(SubscriptionService::class);
            return $service->getFeatureLimits($user);
        } catch (\Exception $e) {
            \Log::error('Error getting feature limits: ' . $e->getMessage());
            return [
                'lists' => 0,
                'urls_per_list' => 0,
                'collaborators' => 0,
                'custom_domains' => false,
                'analytics' => false,
            ];
        }
    }
    
    public function getUsagePercentage($used, $limit)
    {
        if ($limit === -1) return 0; // Unlimited
        if ($limit === 0) return 100; // No limit set
        return min(100, ($used / $limit) * 100);
    }
};
?>

<div class="max-w-7xl mx-auto backdrop-blur-sm bg-white/80 dark:bg-neutral-800/80 shadow-xl rounded-3xl p-6 lg:p-8 mt-8 border border-gray-100/40 dark:border-neutral-700/50 transition-all duration-300 relative overflow-hidden space-y-8">
    <!-- Header with glass morphism effect -->
    <div class="backdrop-blur-sm bg-white/80 dark:bg-zinc-800/80 shadow-xl rounded-2xl p-6 border border-gray-100/40 dark:border-zinc-700/50 transition-all duration-300 relative overflow-hidden">
        <!-- Decorative elements -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-emerald-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-gradient-to-tr from-teal-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
        
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="relative">
                    <h2 class="text-2xl md:text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                        <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-500 to-teal-400">
                            Customer Subscription Overview
                        </span>
                    </h2>
                    <!-- Animated decorative element -->
                    <div class="absolute -bottom-2 left-0 h-1 w-16 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full animate-pulse"></div>
                </div>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Monitor customer subscriptions and usage metrics
                </p>
            </div>
            
            <div class="flex flex-wrap md:flex-nowrap gap-3">
                <a
                    href="{{ route('admin.subscriptions.metrics') }}"
                    class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 bg-white dark:bg-zinc-700/50 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-zinc-600/50 hover:bg-gray-50 dark:hover:bg-zinc-700/80 shadow-sm"
                    wire:navigate
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z" />
                        <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z" />
                    </svg>
                    Subscription Metrics
                </a>
                
                <a
                    href="{{ route('admin.subscriptions.index') }}"
                    class="relative overflow-hidden inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-medium bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white transition-all duration-300 shadow-sm hover:shadow group"
                    wire:navigate
                >
                    <span class="relative z-10 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                            <path d="M8 11a1 1 0 100-2H6a1 1 0 100 2h2zm0 3a1 1 0 100-2H6a1 1 0 100 2h2z" />
                        </svg>
                        View All Subscriptions
                    </span>
                    <!-- Shimmer effect -->
                    <span class="absolute top-0 right-full w-12 h-full bg-white/30 transform rotate-12 translate-x-0 transition-transform duration-1000 ease-out group-hover:translate-x-[400%]"></span>
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Total Customers -->
        <div class="group bg-white/60 dark:bg-zinc-800/40 rounded-xl overflow-hidden border border-gray-200/60 dark:border-zinc-700/40 backdrop-blur-sm shadow-sm transition-all duration-300 hover:shadow-md hover:translate-y-[-2px]">
            <div class="px-5 py-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-emerald-50 dark:bg-emerald-900/20 rounded-full p-3 border border-emerald-100 dark:border-emerald-800/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                        </svg>
                    </div>
                    
                    <div class="ml-4 flex-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                            Total Customers
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                            {{ number_format((int) ($this->stats['total'] ?? 0)) }}
                        </dd>
                    </div>
                </div>
            </div>
            <div class="w-full bg-gradient-to-r from-emerald-500 to-teal-500 h-1 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
        </div>
        
        <!-- Subscribed Customers -->
        <div class="group bg-white/60 dark:bg-zinc-800/40 rounded-xl overflow-hidden border border-gray-200/60 dark:border-zinc-700/40 backdrop-blur-sm shadow-sm transition-all duration-300 hover:shadow-md hover:translate-y-[-2px]">
            <div class="px-5 py-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-50 dark:bg-green-900/20 rounded-full p-3 border border-green-100 dark:border-green-800/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    
                    <div class="ml-4 flex-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                            Subscribed Customers
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                            {{ number_format((int) ($this->stats['subscribed'] ?? 0)) }}
                        </dd>
                    </div>
                </div>
            </div>
            <div class="w-full bg-gradient-to-r from-green-500 to-emerald-500 h-1 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
        </div>
        
        <!-- Trial Customers -->
        <div class="group bg-white/60 dark:bg-zinc-800/40 rounded-xl overflow-hidden border border-gray-200/60 dark:border-zinc-700/40 backdrop-blur-sm shadow-sm transition-all duration-300 hover:shadow-md hover:translate-y-[-2px]">
            <div class="px-5 py-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-50 dark:bg-blue-900/20 rounded-full p-3 border border-blue-100 dark:border-blue-800/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    
                    <div class="ml-4 flex-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                            Trial Customers
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                            {{ number_format((int) ($this->stats['trial'] ?? 0)) }}
                        </dd>
                    </div>
                </div>
            </div>
            <div class="w-full bg-gradient-to-r from-blue-500 to-sky-500 h-1 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
        </div>
        
        <!-- Cancelled Subscriptions -->
        <div class="group bg-white/60 dark:bg-zinc-800/40 rounded-xl overflow-hidden border border-gray-200/60 dark:border-zinc-700/40 backdrop-blur-sm shadow-sm transition-all duration-300 hover:shadow-md hover:translate-y-[-2px]">
            <div class="px-5 py-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-50 dark:bg-red-900/20 rounded-full p-3 border border-red-100 dark:border-red-800/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    
                    <div class="ml-4 flex-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                            Cancelled Subscriptions
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                            {{ number_format((int) ($this->stats['cancelled'] ?? 0)) }}
                        </dd>
                    </div>
                </div>
            </div>
            <div class="w-full bg-gradient-to-r from-red-500 to-pink-500 h-1 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
        </div>
    </div>

    <!-- Filters and Customer Table Section -->
    <div class="backdrop-blur-sm bg-white/80 dark:bg-zinc-800/80 shadow-xl rounded-2xl border border-gray-100/40 dark:border-zinc-700/50 transition-all duration-300 relative overflow-hidden">
        <!-- Decorative elements -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-teal-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-gradient-to-tr from-emerald-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
        
        <!-- Filters -->
        <div class="p-5 lg:p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Search -->
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-emerald-500 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input
                        type="search"
                        wire:model.live="search"
                        placeholder="Search customers..."
                        class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 dark:border-zinc-600 rounded-xl leading-5 bg-white/80 dark:bg-zinc-700/80 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition duration-200"
                    />
                    <div class="absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r from-emerald-500 to-teal-500 transform scale-x-0 group-focus-within:scale-x-100 transition-transform duration-300 origin-left rounded-full"></div>
                </div>

                <!-- Filter dropdown -->
                <div class="relative group">
                    <select
                        wire:model.live="filter"
                        class="appearance-none block w-full py-2.5 px-3 border border-gray-300 dark:border-zinc-600 bg-white/80 dark:bg-zinc-700/80 text-gray-900 dark:text-white rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition duration-200"
                    >
                        <option value="">All Customers</option>
                        <option value="subscribed">Active Subscriptions</option>
                        <option value="trial">Trial Period</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="expired">Expired</option>
                        <option value="no_subscription">No Subscription</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 group-focus-within:text-emerald-500 transition-colors duration-200" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r from-emerald-500 to-teal-500 transform scale-x-0 group-focus-within:scale-x-100 transition-transform duration-300 origin-left rounded-full"></div>
                </div>

                <!-- Per page options -->
                <div class="relative group">
                    <select
                        wire:model.live="perPage"
                        class="appearance-none block w-full py-2.5 px-3 border border-gray-300 dark:border-zinc-600 bg-white/80 dark:bg-zinc-700/80 text-gray-900 dark:text-white rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition duration-200"
                    >
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 group-focus-within:text-emerald-500 transition-colors duration-200" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r from-emerald-500 to-teal-500 transform scale-x-0 group-focus-within:scale-x-100 transition-transform duration-300 origin-left rounded-full"></div>
                </div>
            </div>
        </div>

        <!-- Customers Table -->
        <div class="overflow-x-auto scrollbar-thin scrollbar-thumb-gray-200 dark:scrollbar-thumb-zinc-700 scrollbar-track-transparent">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                <thead>
                    <tr class="bg-gray-50/90 dark:bg-zinc-800/50">
                        <th wire:click="sort('name')" class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors duration-200 select-none">
                            <div class="flex items-center">
                                Customer
                                @if($sortField === 'name')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 h-4 w-4 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                                        @if($sortDirection === 'asc')
                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                        @else
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        @endif
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Subscription
                        </th>
                        <th class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Usage
                        </th>
                        <th class="px-6 py-3.5 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white/60 dark:bg-zinc-800/60 backdrop-blur-sm divide-y divide-gray-200 dark:divide-zinc-700">
                    @forelse($this->customers as $customer)
                        @php
                            $usage = $this->getUsageStats($customer);
                            $limits = $this->getFeatureLimits($customer);
                        @endphp
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-zinc-700/30 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-9 w-9 rounded-full bg-gradient-to-br from-emerald-500/80 to-teal-500/80 text-white flex items-center justify-center text-sm font-medium">
                                        {{ strtoupper(substr($customer->name, 0, 2)) }}
                                    </div>
                                    <div class="ml-3">
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
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $customer->subscription->plan->name }}
                                        <span class="text-xs text-gray-500 dark:text-gray-400">({{ ucfirst($customer->subscription->interval) }})</span>
                                    </div>
                                    <div class="mt-1">
                                        @if($customer->subscription->status === 'active')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800/20 dark:text-green-400 border border-green-200 dark:border-green-800/30">
                                                <svg class="mr-1.5 h-2 w-2 text-green-500" fill="currentColor" viewBox="0 0 8 8">
                                                    <circle cx="4" cy="4" r="3" />
                                                </svg>
                                                Active
                                            </span>
                                        @elseif($customer->subscription->status === 'trialing')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800/20 dark:text-blue-400 border border-blue-200 dark:border-blue-800/30">
                                                <svg class="mr-1.5 h-2 w-2 text-blue-500" fill="currentColor" viewBox="0 0 8 8">
                                                    <circle cx="4" cy="4" r="3" />
                                                </svg>
                                                Trial
                                            </span>
                                        @elseif($customer->subscription->status === 'cancelled')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-800/20 dark:text-red-400 border border-red-200 dark:border-red-800/30">
                                                <svg class="mr-1.5 h-2 w-2 text-red-500" fill="currentColor" viewBox="0 0 8 8">
                                                    <circle cx="4" cy="4" r="3" />
                                                </svg>
                                                Cancelled
                                            </span>
                                        @elseif($customer->subscription->status === 'expired')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                                                <svg class="mr-1.5 h-2 w-2 text-gray-500" fill="currentColor" viewBox="0 0 8 8">
                                                    <circle cx="4" cy="4" r="3" />
                                                </svg>
                                                Expired
                                            </span>
                                        @endif
                                    </div>
                                    @if($customer->subscription->trial_ends_at && $customer->subscription->trial_ends_at->isFuture())
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                </svg>
                                                Trial ends {{ $customer->subscription->trial_ends_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                                        <svg class="mr-1.5 h-2 w-2 text-gray-500" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3" />
                                        </svg>
                                        No subscription
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-3">
                                    <!-- Lists Usage -->
                                    <div class="group">
                                        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1.5">
                                            <span class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                                                </svg>
                                                Lists
                                            </span>
                                            <span class="font-medium">
                                                {{ $usage['lists'] ?? 0 }} / 
                                                <span class="{{ $limits['lists'] === -1 ? 'text-emerald-500 dark:text-emerald-400' : '' }}">
                                                    {{ $limits['lists'] === -1 ? '∞' : $limits['lists'] }}
                                                </span>
                                            </span>
                                        </div>
                                        <div class="w-full h-1.5 bg-gray-200/70 dark:bg-gray-700/50 rounded-full overflow-hidden transition-all duration-300 group-hover:h-2">
                                            <div 
                                                class="h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full transition-all duration-300 ease-out"
                                                style="width: {{ $this->getUsagePercentage($usage['lists'] ?? 0, $limits['lists']) }}%"
                                            ></div>
                                        </div>
                                    </div>

                                    <!-- URLs per List Usage -->
                                    <div class="group">
                                        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1.5">
                                            <span class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd" />
                                                </svg>
                                                URLs/List
                                            </span>
                                            <span class="font-medium">
                                                {{ $usage['urls_per_list'] ?? 0 }} / 
                                                <span class="{{ $limits['urls_per_list'] === -1 ? 'text-emerald-500 dark:text-emerald-400' : '' }}">
                                                    {{ $limits['urls_per_list'] === -1 ? '∞' : $limits['urls_per_list'] }}
                                                </span>
                                            </span>
                                        </div>
                                        <div class="w-full h-1.5 bg-gray-200/70 dark:bg-gray-700/50 rounded-full overflow-hidden transition-all duration-300 group-hover:h-2">
                                            <div 
                                                class="h-full bg-gradient-to-r from-blue-500 to-sky-500 rounded-full transition-all duration-300 ease-out"
                                                style="width: {{ $this->getUsagePercentage($usage['urls_per_list'] ?? 0, $limits['urls_per_list']) }}%"
                                            ></div>
                                        </div>
                                    </div>

                                    <!-- Collaborators Usage -->
                                    <div class="group">
                                        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1.5">
                                            <span class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1 text-purple-500" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                                                </svg>
                                                Collaborators
                                            </span>
                                            <span class="font-medium">
                                                {{ $usage['collaborators'] ?? 0 }} / 
                                                <span class="{{ $limits['collaborators'] === -1 ? 'text-emerald-500 dark:text-emerald-400' : '' }}">
                                                    {{ $limits['collaborators'] === -1 ? '∞' : $limits['collaborators'] }}
                                                </span>
                                            </span>
                                        </div>
                                        <div class="w-full h-1.5 bg-gray-200/70 dark:bg-gray-700/50 rounded-full overflow-hidden transition-all duration-300 group-hover:h-2">
                                            <div 
                                                class="h-full bg-gradient-to-r from-purple-500 to-indigo-500 rounded-full transition-all duration-300 ease-out"
                                                style="width: {{ $this->getUsagePercentage($usage['collaborators'] ?? 0, $limits['collaborators']) }}%"
                                            ></div>
                                        </div>
                                    </div>

                                    <!-- Feature indicators -->
                                    <div class="flex space-x-2 pt-1">
                                        @if($limits['custom_domains'])
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                                                </svg>
                                                Custom Domains
                                            </span>
                                        @endif
                                        @if($limits['analytics'])
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z" />
                                                    <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z" />
                                                </svg>
                                                Analytics
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @if($customer->subscription)
                                    <a 
                                        href="{{ route('admin.subscriptions.show', $customer->subscription) }}" 
                                        class="group inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:hover:bg-emerald-900/40 transition-colors duration-200"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 group-hover:animate-pulse" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                        </svg>
                                        View Details
                                    </a>
                                @else
                                    <a 
                                        href="{{ route('admin.customers.show', $customer) }}" 
                                        class="group inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40 transition-colors duration-200"
                                        wire:navigate
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 group-hover:animate-pulse" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd" />
                                        </svg>
                                        View Customer
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <p class="text-sm font-medium">No customers found</p>
                                    <p class="text-xs mt-1">Try adjusting your search or filter criteria</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 bg-gray-50/80 dark:bg-zinc-800/50 border-t border-gray-200/60 dark:border-zinc-700/50 rounded-b-2xl">
            {{ $this->customers->links() }}
        </div>
    </div>
</div>

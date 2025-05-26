<?php

use function Livewire\Volt\{state, mount, computed};
use App\Models\Plan;

state([
    'plan' => null,
    'selectedVersion' => null,
]);

mount(function (Plan $plan) {
    $this->plan = $plan;
    $this->selectedVersion = $plan->getCurrentVersion();
});

$changeVersion = function ($versionId) {
    $this->selectedVersion = $this->plan->versions()->findOrFail($versionId);
};

$versions = computed(function () {
    return $this->plan->versions()->orderByDesc('created_at')->get();
});

$subscriptionCount = computed(function () {
    return $this->plan->subscriptions()->count();
});

$activeSubscriptionCount = computed(function () {
    return $this->plan->subscriptions()->where('status', 'active')->count();
});

?>

<div>
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ $plan->name }}
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $plan->description }}
            </p>
        </div>
        <div class="mt-4 sm:mt-0 space-x-3">
            <a
                href="{{ route('admin.plans.edit', $plan) }}"
                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
            >
                Edit Plan
            </a>
            <a
                href="{{ route('admin.plans.index') }}"
                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 dark:bg-zinc-800 dark:border-zinc-700 dark:text-gray-300"
            >
                Back to List
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <flux:stat-card
            title="Total Subscriptions"
            :value="$subscriptionCount"
            icon="users"
            trend="none"
        />

        <flux:stat-card
            title="Active Subscriptions"
            :value="$activeSubscriptionCount"
            icon="check-circle"
            type="success"
            trend="none"
        />
        
        <flux:stat-card
            title="Total Versions"
            :value="$versions->count()"
            icon="document-duplicate"
            type="info"
            trend="none"
        />
        
        <flux:stat-card
            title="Monthly Revenue"
            :value="'$' . number_format($activeSubscriptionCount * $plan->monthly_price, 2)"
            icon="currency-dollar"
            type="warning"
            trend="none"
        />
    </div>

    <!-- Plan Details -->
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Current Version Details --}}
        <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg divide-y dark:divide-zinc-700">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Current Version Details</h3>

                <div class="space-y-4">
                    <!-- Version Info -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Version</h4>
                        <select
                            wire:model.live="selectedVersion"
                            wire:change="changeVersion($event.target.value)"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm rounded-md dark:bg-zinc-800 dark:border-zinc-700"
                        >
                            @foreach($versions as $version)
                                <option value="{{ $version->id }}">
                                    v{{ $version->version }} {{ $version->is_active ? '(Active)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Valid Period -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Valid Period</h4>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            @if($selectedVersion->valid_from)
                                From: {{ $selectedVersion->valid_from->format('M d, Y H:i') }}<br>
                                @if($selectedVersion->valid_until)
                                    Until: {{ $selectedVersion->valid_until->format('M d, Y H:i') }}
                                @else
                                    No end date
                                @endif
                            @else
                                No validity period set
                            @endif
                        </p>
                    </div>

                    <!-- Pricing -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Monthly Price</h4>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                ${{ number_format($selectedVersion->monthly_price, 2) }}
                            </p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Yearly Price</h4>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                ${{ number_format($selectedVersion->yearly_price, 2) }}
                            </p>
                        </div>
                    </div>

                    <!-- PayPal Integration -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">PayPal Integration</h4>
                        <div class="mt-2 grid grid-cols-1 gap-2">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Monthly Plan ID:</p>
                                <p class="text-sm text-gray-900 dark:text-white font-mono">
                                    {{ $selectedVersion->paypal_monthly_plan_id ?? 'Not set' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Yearly Plan ID:</p>
                                <p class="text-sm text-gray-900 dark:text-white font-mono">
                                    {{ $selectedVersion->paypal_yearly_plan_id ?? 'Not set' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Features & Limits --}}
        <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg divide-y dark:divide-zinc-700">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Features & Limits</h3>

                <div class="space-y-6">
                    <!-- Limits -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Resource Limits</h4>
                        <div class="mt-2 grid grid-cols-3 gap-4">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Lists</p>
                                <p class="text-sm text-gray-900 dark:text-white">
                                    {{ $plan->max_lists === -1 ? '∞' : $plan->max_lists }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">URLs per List</p>
                                <p class="text-sm text-gray-900 dark:text-white">
                                    {{ $plan->max_urls_per_list === -1 ? '∞' : $plan->max_urls_per_list }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Team Members</p>
                                <p class="text-sm text-gray-900 dark:text-white">
                                    {{ $plan->max_team_members === -1 ? '∞' : $plan->max_team_members }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Features List -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Features</h4>
                        <ul class="mt-2 space-y-2">
                            @foreach($selectedVersion->features as $feature)
                                <li class="flex items-start">
                                    <svg class="h-5 w-5 text-emerald-500 dark:text-emerald-400 mt-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Version History -->
    <div class="mt-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Version History</h3>
        <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg">
            <ul class="divide-y divide-gray-200 dark:divide-zinc-700">
                @foreach($versions as $version)
                    <li class="px-4 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                                    v{{ $version->version }}
                                    @if($version->is_active)
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                                            Active
                                        </span>
                                    @endif
                                </h4>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Valid from {{ $version->valid_from?->format('M d, Y H:i') ?? 'Not set' }}
                                    @if($version->valid_until)
                                        until {{ $version->valid_until->format('M d, Y H:i') }}
                                    @endif
                                </p>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $version->created_at->format('M d, Y H:i') }}
                            </div>
                        </div>
                        <div class="mt-2 grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Monthly:</span>
                                <span class="ml-1 text-gray-900 dark:text-white">${{ number_format($version->monthly_price, 2) }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Yearly:</span>
                                <span class="ml-1 text-gray-900 dark:text-white">${{ number_format($version->yearly_price, 2) }}</span>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

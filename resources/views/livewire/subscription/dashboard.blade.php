<?php

use App\Models\Plan;
use App\Services\SubscriptionService;
use App\Services\UsageTrackingService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] #[Title('Subscription Dashboard')] class extends Component
{
    public $subscription = null;
    public $usage = [];
    public $error = null;

    public function mount(SubscriptionService $subscriptionService, UsageTrackingService $usageTrackingService)
    {
        $user = auth()->user();
        $state = $subscriptionService->getSubscriptionState($user);
        $this->subscription = $state;
        $this->usage = $usageTrackingService->getTotalUsage($user);
    }

    public function getFeatureLimit(string $feature)
    {
        $subscriptionService = app(SubscriptionService::class);
        $limits = $subscriptionService->getFeatureLimits(auth()->user());
        return $limits[$feature] ?? 0;
    }

    public function getRemainingQuota(string $feature)
    {
        $subscriptionService = app(SubscriptionService::class);
        $currentUsage = $this->usage[$feature] ?? 0;
        return $subscriptionService->getRemainingQuota(auth()->user(), $feature, $currentUsage);
    }

    public function getUsagePercentage(string $feature)
    {
        $limit = $this->getFeatureLimit($feature);
        if ($limit === -1) return 0;
        if ($limit === 0) return 100;
        
        $currentUsage = $this->usage[$feature] ?? 0;
        return min(100, ($currentUsage / $limit) * 100);
    }
    
    public function resumeSubscription()
    {
        // Implementation for resume subscription
    }
    
    public function cancelSubscription()
    {
        // Implementation for cancel subscription
    }
}

?>

<div>
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        <!-- Current Plan Status -->
        <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-xl sm:rounded-lg p-6 mb-8">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-2xl font-semibold text-emerald-900 dark:text-emerald-100">
                        Current Plan: {{ $subscription['plan']->name ?? 'Free' }}
                    </h2>
                    <p class="mt-2 text-emerald-700 dark:text-emerald-300">
                        @if($subscription['status'] === 'active')
                            Active subscription
                        @elseif($subscription['status'] === 'trialing')
                            Trial ends in {{ now()->diffInDays($subscription['trial_ends_at']) }} days
                        @elseif($subscription['status'] === 'cancelled_pending')
                            Subscription will end on {{ $subscription['ends_at']->format('M d, Y') }}
                        @elseif($subscription['status'] === 'expired')
                            Subscription expired
                        @else
                            No active subscription
                        @endif
                    </p>
                </div>
                <div class="flex gap-4">
                    @if($subscription['status'] === 'cancelled_pending')
                        <button wire:click="resumeSubscription" class="btn btn-primary">
                            Resume Subscription
                        </button>
                    @elseif($subscription['status'] === 'active')
                        <button wire:click="cancelSubscription" class="btn btn-secondary">
                            Cancel Subscription
                        </button>
                        <a href="{{ route('plans') }}" class="btn btn-primary">
                            Change Plan
                        </a>
                    @else
                        <a href="{{ route('plans') }}" class="btn btn-primary">
                            View Plans
                        </a>
                    @endif
                </div>
            </div>

            @if($subscription['plan'])
                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Lists Usage -->
                    <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-lg p-4">
                        <h3 class="text-sm font-medium text-emerald-900 dark:text-emerald-100">URL Lists</h3>
                        <div class="mt-2">
                            <div class="flex justify-between text-sm text-emerald-700 dark:text-emerald-300">
                                <span>{{ $usage['lists'] ?? 0 }} used</span>
                                <span>{{ $this->getFeatureLimit('lists') === -1 ? 'Unlimited' : $this->getFeatureLimit('lists') . ' total' }}</span>
                            </div>
                            <div class="mt-2 relative">
                                <div class="overflow-hidden h-2 text-xs flex rounded bg-emerald-200 dark:bg-emerald-900/50">
                                    <div style="width: {{ $this->getUsagePercentage('lists') }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-emerald-500">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- URLs per List Usage -->
                    <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-lg p-4">
                        <h3 class="text-sm font-medium text-emerald-900 dark:text-emerald-100">URLs per List</h3>
                        <div class="mt-2">
                            <div class="flex justify-between text-sm text-emerald-700 dark:text-emerald-300">
                                <span>{{ $usage['urls_per_list'] ?? 0 }} used</span>
                                <span>{{ $this->getFeatureLimit('urls_per_list') === -1 ? 'Unlimited' : $this->getFeatureLimit('urls_per_list') . ' total' }}</span>
                            </div>
                            <div class="mt-2 relative">
                                <div class="overflow-hidden h-2 text-xs flex rounded bg-emerald-200 dark:bg-emerald-900/50">
                                    <div style="width: {{ $this->getUsagePercentage('urls_per_list') }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-emerald-500">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Collaborators Usage -->
                    <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-lg p-4">
                        <h3 class="text-sm font-medium text-emerald-900 dark:text-emerald-100">Collaborators</h3>
                        <div class="mt-2">
                            <div class="flex justify-between text-sm text-emerald-700 dark:text-emerald-300">
                                <span>{{ $usage['collaborators'] ?? 0 }} used</span>
                                <span>{{ $this->getFeatureLimit('collaborators') === -1 ? 'Unlimited' : $this->getFeatureLimit('collaborators') . ' total' }}</span>
                            </div>
                            <div class="mt-2 relative">
                                <div class="overflow-hidden h-2 text-xs flex rounded bg-emerald-200 dark:bg-emerald-900/50">
                                    <div style="width: {{ $this->getUsagePercentage('collaborators') }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-emerald-500">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Plan Features -->
                <div class="mt-8">
                    <h3 class="text-lg font-medium text-emerald-900 dark:text-emerald-100">Plan Features</h3>
                    <ul class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($subscription['plan']->features as $feature)
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-emerald-500 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span class="ml-2 text-emerald-700 dark:text-emerald-300">{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Billing History -->
                <div class="mt-8">
                    <h3 class="text-lg font-medium text-emerald-900 dark:text-emerald-100">Billing History</h3>
                    <div class="mt-4">
                        <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-lg p-4">
                            <div class="flex justify-between">
                                <div>
                                    <p class="text-sm font-medium text-emerald-900 dark:text-emerald-100">Current Period</p>
                                    <p class="text-sm text-emerald-700 dark:text-emerald-300">
                                        {{ $subscription['current_period_starts_at']?->format('M d, Y') }} -
                                        {{ $subscription['current_period_ends_at']?->format('M d, Y') }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-emerald-900 dark:text-emerald-100">Next Payment</p>
                                    <p class="text-sm text-emerald-700 dark:text-emerald-300">
                                        ${{ number_format($subscription['plan']->getPrice($subscription['interval']), 2) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

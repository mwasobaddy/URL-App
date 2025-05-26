<?php

use Livewire\Volt\Component;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use App\Services\PayPalSubscriptionService;

new class extends Component {
    public $subscription = null;
    public $paypalDetails = null;
    public $loading = false;
    public $error = null;
    
    public function mount(Subscription $subscription, SubscriptionService $subscriptionService) {
        $this->subscription = $subscription->load(['user', 'plan', 'planVersion']);
        $this->refreshPayPalDetails();
    }
    
    public function metrics() {
        $user = $this->subscription->user;
        $service = app(SubscriptionService::class);
        
        return [
            'lists_count' => $user->urlLists()->count(),
            'lists_limit' => $service->getFeatureLimits($user)['lists'] ?? 0,
            'urls_count' => $user->urlLists()->withCount('urls')->get()->sum('urls_count'),
            'urls_per_list' => $service->getFeatureLimits($user)['urls_per_list'] ?? 0,
            'collaborators_count' => $user->urlLists()->withCount('collaborators')->get()->sum('collaborators_count'),
            'collaborators_limit' => $service->getFeatureLimits($user)['collaborators'] ?? 0,
        ];
    }
    
    public function refreshPayPalDetails() {
        $this->loading = true;
        $this->error = null;
        
        try {
            $paypalService = app(PayPalSubscriptionService::class);
            $this->paypalDetails = $paypalService->getSubscription($this->subscription->paypal_subscription_id);
        } catch (\Exception $e) {
            $this->error = 'Failed to fetch PayPal subscription details.';
        }
        
        $this->loading = false;
    }
    
    public function cancelSubscription() {
        $this->loading = true;
        $this->error = null;
        
        try {
            $paypalService = app(PayPalSubscriptionService::class);
            if ($paypalService->cancelSubscription($this->subscription)) {
                $this->subscription->cancel();
                $this->refreshPayPalDetails();
                $this->dispatch('subscription-updated');
            } else {
                $this->error = 'Failed to cancel subscription.';
            }
        } catch (\Exception $e) {
            $this->error = 'Failed to cancel subscription.';
        }
        
        $this->loading = false;
    }
    
    public function resumeSubscription() {
        $this->loading = true;
        $this->error = null;
        
        try {
            $this->subscription->resume();
            $this->refreshPayPalDetails();
            $this->dispatch('subscription-updated');
        } catch (\Exception $e) {
            $this->error = 'Failed to resume subscription.';
        }
        
        $this->loading = false;
    }
}

?>

<div>
    {{-- Header --}}
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                Subscription Details
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Manage subscription #{{ $subscription->id }}
            </p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a 
                href="{{ route('admin.subscriptions.index') }}" 
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50"
            >
                Back to List
            </a>
        </div>
    </div>

    @if($error)
        <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-lg">
            {{ $error }}
        </div>
    @endif

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Subscription Info --}}
        <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg divide-y dark:divide-zinc-700">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Subscription Information</h3>
                
                <div class="mt-4 space-y-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</h4>
                        <div class="mt-1">
                            <x-subscription-status :status="$subscription->status" />
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Plan</h4>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $subscription->plan->name }} ({{ $subscription->planVersion->name }})
                        </p>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Billing Cycle</h4>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ ucfirst($subscription->interval) }}
                        </p>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Current Period</h4>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $subscription->current_period_starts_at?->format('M d, Y') }} - 
                            {{ $subscription->current_period_ends_at?->format('M d, Y') }}
                        </p>
                    </div>

                    @if($subscription->trial_ends_at)
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Trial Period</h4>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                Ends {{ $subscription->trial_ends_at->format('M d, Y') }}
                            </p>
                        </div>
                    @endif

                    @if($subscription->cancelled_at)
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Cancelled At</h4>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $subscription->cancelled_at->format('M d, Y') }}
                            </p>
                        </div>
                    @endif
                </div>

                <div class="mt-6">
                    @if($subscription->isActive())
                        @if(!$subscription->isCancelled())
                            <button
                                wire:click="cancelSubscription"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                            >
                                Cancel Subscription
                            </button>
                        @else
                            <button
                                wire:click="resumeSubscription"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
                            >
                                Resume Subscription
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- Customer Info --}}
        <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg divide-y dark:divide-zinc-700">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Customer Information</h3>
                
                <div class="mt-4 space-y-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</h4>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $subscription->user->name }}
                        </p>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</h4>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $subscription->user->email }}
                        </p>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Member Since</h4>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $subscription->user->created_at->format('M d, Y') }}
                        </p>
                    </div>
                </div>

                <div class="mt-6">
                    <a
                        href="{{ route('admin.users.show', $subscription->user) }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
                    >
                        View Customer Details
                    </a>
                </div>
            </div>
        </div>

        {{-- Usage Stats --}}
        <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg divide-y dark:divide-zinc-700">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Usage Statistics</h3>
                
                <div class="mt-4 grid grid-cols-1 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">URL Lists</h4>
                        <div class="mt-2">
                            <div class="flex items-center justify-between">
                                <p class="text-sm text-gray-900 dark:text-white">
                                    {{ $this->metrics()['lists_count'] }} / 
                                    {{ $this->metrics()['lists_limit'] === -1 ? '∞' : $this->metrics()['lists_limit'] }}
                                </p>
                                @if($this->metrics()['lists_limit'] !== -1)
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ number_format(($this->metrics()['lists_count'] / $this->metrics()['lists_limit']) * 100, 0) }}%
                                    </p>
                                @endif
                            </div>
                            @if($this->metrics()['lists_limit'] !== -1)
                                <div class="mt-1 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div 
                                        class="bg-emerald-600 h-2 rounded-full" 
                                        style="width: {{ min(($this->metrics()['lists_count'] / $this->metrics()['lists_limit']) * 100, 100) }}%"
                                    ></div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">URLs per List</h4>
                        <div class="mt-2">
                            <p class="text-sm text-gray-900 dark:text-white">
                                Max {{ $this->metrics()['urls_per_list'] === -1 ? '∞' : $this->metrics()['urls_per_list'] }} URLs per list
                            </p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Total URLs: {{ $this->metrics()['urls_count'] }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Collaborators</h4>
                        <div class="mt-2">
                            <div class="flex items-center justify-between">
                                <p class="text-sm text-gray-900 dark:text-white">
                                    {{ $this->metrics()['collaborators_count'] }} / 
                                    {{ $this->metrics()['collaborators_limit'] === -1 ? '∞' : $this->metrics()['collaborators_limit'] }}
                                </p>
                                @if($this->metrics()['collaborators_limit'] !== -1)
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ number_format(($this->metrics()['collaborators_count'] / $this->metrics()['collaborators_limit']) * 100, 0) }}%
                                    </p>
                                @endif
                            </div>
                            @if($this->metrics()['collaborators_limit'] !== -1)
                                <div class="mt-1 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div 
                                        class="bg-emerald-600 h-2 rounded-full" 
                                        style="width: {{ min(($this->metrics()['collaborators_count'] / $this->metrics()['collaborators_limit']) * 100, 100) }}%"
                                    ></div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PayPal Details --}}
        <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg divide-y dark:divide-zinc-700">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">PayPal Details</h3>
                    <button
                        wire:click="refreshPayPalDetails"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
                    >
                        <span wire:loading.remove wire:target="refreshPayPalDetails">Refresh</span>
                        <span wire:loading wire:target="refreshPayPalDetails">Loading...</span>
                    </button>
                </div>
                
                <div class="mt-4">
                    @if($loading)
                        <div class="text-center py-4">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-500 mx-auto"></div>
                        </div>
                    @elseif($paypalDetails)
                        <div class="space-y-4">
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">PayPal Status</h4>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ ucfirst($paypalDetails['status']) }}
                                </p>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">PayPal Subscription ID</h4>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ $subscription->paypal_subscription_id }}
                                </p>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">PayPal Plan ID</h4>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ $subscription->paypal_plan_id }}
                                </p>
                            </div>

                            @if(isset($paypalDetails['billing_info']))
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Payment</h4>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                        {{ $paypalDetails['billing_info']['last_payment']['amount']['value'] }}
                                        {{ $paypalDetails['billing_info']['last_payment']['amount']['currency_code'] }}
                                        on {{ \Carbon\Carbon::parse($paypalDetails['billing_info']['last_payment']['time'])->format('M d, Y') }}
                                    </p>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Next Billing Time</h4>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                        {{ \Carbon\Carbon::parse($paypalDetails['billing_info']['next_billing_time'])->format('M d, Y') }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Failed to load PayPal details. Click refresh to try again.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<?php

use App\Services\PayPalSubscriptionService;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;
use Livewire\Volt\Component;

new class extends Component {
    // State as public properties
    public $subscription = null;
    public $status = 'pending';
    public $error = null;
    
    // Mount method
    public function mount(Subscription $subscription)
    {
        $this->subscription = $subscription;
        $this->activateSubscription();
    }
    
    // Action as method
    public function activateSubscription()
    {
        try {
            $paypalService = app(PayPalSubscriptionService::class);
            $paypalService->processSuccessfulSubscription($this->subscription);
            
            $this->status = 'success';
            $this->dispatch('subscription-activated', subscriptionId: $this->subscription->id);
            
            $this->redirect(route('dashboard'), navigate: true);
        } catch (\Exception $e) {
            Log::error('Subscription activation failed', [
                'subscription_id' => $this->subscription->id,
                'error' => $e->getMessage()
            ]);
            
            $this->status = 'failed';
            $this->error = 'Failed to activate subscription. Please try again or contact support.';
        }
    }
}

?>

<div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900">
    <div class="max-w-md w-full p-6 bg-white dark:bg-gray-800 rounded-lg shadow-lg">
        <div class="text-center">
            @if($status === 'pending')
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-emerald-500 mx-auto"></div>
                <h2 class="mt-4 text-xl font-semibold text-gray-900 dark:text-white">
                    Activating Your Subscription
                </h2>
                <p class="mt-2 text-gray-600 dark:text-gray-300">
                    Please wait while we activate your subscription...
                </p>
            @elseif($status === 'success')
                <svg class="w-12 h-12 text-emerald-500 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <h2 class="mt-4 text-xl font-semibold text-gray-900 dark:text-white">
                    Subscription Activated!
                </h2>
                <p class="mt-2 text-gray-600 dark:text-gray-300">
                    Your subscription has been successfully activated. Redirecting...
                </p>
            @else
                <svg class="w-12 h-12 text-red-500 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <h2 class="mt-4 text-xl font-semibold text-gray-900 dark:text-white">
                    Activation Failed
                </h2>
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                    {{ $error }}
                </p>
                <div class="mt-6 space-x-2">
                    <button wire:click="activateSubscription" class="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700">
                        Try Again
                    </button>
                    <a href="{{ route('support') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
                        Contact Support
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

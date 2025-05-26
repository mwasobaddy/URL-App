<?php

use Livewire\Volt\Component;
use App\Models\Plan;
use App\Services\PayPalSubscriptionService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

new #[Layout('layouts.app')] #[Title('Subscribe')] class extends Component
{
    public ?Plan $plan = null;
    public string $interval = 'monthly';
    public ?string $paypalSubscriptionId = null;
    public ?string $error = null;
    
    public function mount(Plan $plan, string $interval = 'monthly')
    {
        $this->plan = $plan;
        $this->interval = $interval;
    }
    
    public function subscribe()
    {
        try {
            $paypalService = new PayPalSubscriptionService();
            $subscription = $paypalService->createSubscription(
                auth()->user(),
                $this->plan,
                $this->interval
            );

            return redirect()->to($subscription->approval_url);
        } catch (\Exception $e) {
            $this->error = 'Failed to create subscription. Please try again.';
        }
    }
}

?>

<div>
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm p-6 border border-emerald-100 dark:border-emerald-900/50">
            <h2 class="text-2xl font-semibold text-emerald-900 dark:text-emerald-100 mb-6">Subscribe to {{ $plan->name }}</h2>

            @if ($error)
            <div class="rounded-md bg-red-50 dark:bg-red-900/30 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700 dark:text-red-300">{{ $error }}</p>
                    </div>
                </div>
            </div>
            @endif

            <div class="mb-8">
                <h3 class="text-lg font-medium text-emerald-900 dark:text-emerald-100">Plan Details</h3>
                <div class="mt-4 space-y-4">
                    <div class="flex justify-between">
                        <span class="text-emerald-700 dark:text-emerald-300">Plan:</span>
                        <span class="font-medium text-emerald-900 dark:text-emerald-100">{{ $plan->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-emerald-700 dark:text-emerald-300">Billing:</span>
                        <span class="font-medium text-emerald-900 dark:text-emerald-100">{{ ucfirst($interval) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-emerald-700 dark:text-emerald-300">Amount:</span>
                        <span class="font-medium text-emerald-900 dark:text-emerald-100">${{ number_format($interval === 'yearly' ? $plan->yearly_price : $plan->monthly_price, 2) }}/{{ $interval === 'yearly' ? 'year' : 'month' }}</span>
                    </div>
                </div>
            </div>

            <div class="border-t border-emerald-100 dark:border-emerald-900/50 pt-8">
                <h3 class="text-lg font-medium text-emerald-900 dark:text-emerald-100 mb-4">Features</h3>
                <ul class="space-y-3">
                    @foreach ($plan->features as $feature)
                    <li class="flex">
                        <svg class="flex-shrink-0 w-5 h-5 text-emerald-500 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span class="ml-3 text-sm text-emerald-700 dark:text-emerald-300">{{ $feature }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>

            <div class="mt-8">
                <button wire:click="subscribe" class="w-full bg-emerald-600 dark:bg-emerald-500 py-2 px-4 text-white text-center rounded-md hover:bg-emerald-700 dark:hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                    <span wire:loading.remove wire:target="subscribe">Subscribe Now</span>
                    <span wire:loading wire:target="subscribe">Processing...</span>
                </button>
            </div>
        </div>
    </div>
</div>

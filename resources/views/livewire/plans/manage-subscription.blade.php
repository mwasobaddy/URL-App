<?php

use App\Models\Subscription;
use App\Services\PayPalSubscriptionService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] #[Title('Manage Subscription')] class extends Component
{
    public $subscription = null;
    public $error = null;

    public function mount()
    {
        $this->subscription = auth()->user()->subscription;
    }

    public function cancelSubscription()
    {
        try {
            $paypalService = new PayPalSubscriptionService();
            $success = $paypalService->cancelSubscription($this->subscription);

            if ($success) {
                session()->flash('success', 'Your subscription has been cancelled.');
                return redirect()->route('subscription.cancelled');
            }

            $this->error = 'Failed to cancel subscription. Please try again.';
        } catch (\Exception $e) {
            $this->error = 'Failed to cancel subscription. Please try again.';
        }
    }

    public function resumeSubscription()
    {
        try {
            $this->subscription->resume();
            session()->flash('success', 'Your subscription has been resumed.');
            return redirect()->route('subscription.resumed');
        } catch (\Exception $e) {
            $this->error = 'Failed to resume subscription. Please try again.';
        }
    }
}

?>

<div>
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm p-6 border border-emerald-100 dark:border-emerald-900/50">
            <h2 class="text-2xl font-semibold text-emerald-900 dark:text-emerald-100 mb-6">Manage Subscription</h2>

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

            @if ($subscription)
            <div class="space-y-6">
                <!-- Current Plan -->
                <div>
                    <h3 class="text-lg font-medium text-emerald-900 dark:text-emerald-100">Current Plan</h3>
                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm text-emerald-700 dark:text-emerald-300">Plan</span>
                            <p class="mt-1 font-medium text-emerald-900 dark:text-emerald-100">{{ $subscription->plan->name }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-emerald-700 dark:text-emerald-300">Status</span>
                            <p class="mt-1">
                                @if ($subscription->isActive())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300">
                                        Inactive
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <span class="text-sm text-emerald-700 dark:text-emerald-300">Billing Cycle</span>
                            <p class="mt-1 font-medium text-emerald-900 dark:text-emerald-100">{{ ucfirst($subscription->interval) }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-emerald-700 dark:text-emerald-300">Next Payment</span>
                            <p class="mt-1 font-medium text-emerald-900 dark:text-emerald-100">
                                {{ $subscription->current_period_ends_at?->format('M d, Y') ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="border-t border-emerald-100 dark:border-emerald-900/50 pt-6">
                    <h3 class="text-lg font-medium text-emerald-900 dark:text-emerald-100 mb-4">Subscription Management</h3>
                    
                    @if ($subscription->isActive())
                        @if ($subscription->isCancelled())
                            <button wire:click="resumeSubscription" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                                <span wire:loading.remove wire:target="resumeSubscription">Resume Subscription</span>
                                <span wire:loading wire:target="resumeSubscription">Processing...</span>
                            </button>
                        @else
                            <button wire:click="cancelSubscription" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-emerald-700 bg-emerald-100 hover:bg-emerald-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                                <span wire:loading.remove wire:target="cancelSubscription">Cancel Subscription</span>
                                <span wire:loading wire:target="cancelSubscription">Processing...</span>
                            </button>
                        @endif
                    @else
                        <a href="{{ route('plans') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                            Choose a Plan
                        </a>
                    @endif
                </div>
            </div>
            @else
            <div class="text-center">
                <p class="text-emerald-700 dark:text-emerald-300">You don't have an active subscription.</p>
                <a href="{{ route('plans') }}" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                    Choose a Plan
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

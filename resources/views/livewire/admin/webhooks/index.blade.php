<?php

use Livewire\Volt\Component;
use App\Services\PayPalAPIService;

new class extends Component {
    public string $webhookId;
    public string $webhookUrl;
    public array $events = [];
    protected $paypalApi;

    public function mount(PayPalAPIService $paypalApi)
    {
        $this->paypalApi = $paypalApi;
        $this->webhookId = config('paypal.webhook_id') ?? '';
        $this->webhookUrl = route('subscription.webhook');
        $this->loadEvents();
    }

    public function loadEvents()
    {
        try {
            // Required webhook events for PayPal subscriptions
            $this->events = [
                'BILLING.SUBSCRIPTION.CREATED' => 'Subscription created',
                'BILLING.SUBSCRIPTION.ACTIVATED' => 'Subscription activated',
                'BILLING.SUBSCRIPTION.UPDATED' => 'Subscription updated',
                'BILLING.SUBSCRIPTION.CANCELLED' => 'Subscription cancelled',
                'BILLING.SUBSCRIPTION.SUSPENDED' => 'Subscription suspended',
                'BILLING.SUBSCRIPTION.EXPIRED' => 'Subscription expired',
                'PAYMENT.SALE.COMPLETED' => 'Payment completed',
                'PAYMENT.SALE.REFUNDED' => 'Payment refunded',
                'PAYMENT.SALE.REVERSED' => 'Payment reversed',
            ];
        } catch (\Exception $e) {
            $this->addError('webhook', 'Failed to load webhook events: ' . $e->getMessage());
        }
    }
}; ?>

<div class="max-w-7xl mx-auto backdrop-blur-sm bg-white/80 dark:bg-neutral-800/80 shadow-xl rounded-3xl p-6 lg:p-8 mt-8 border border-gray-100/40 dark:border-neutral-700/50 transition-all duration-300 relative overflow-hidden space-y-8">

    <!-- Header Card -->
    <div class="backdrop-blur-sm bg-white/80 dark:bg-zinc-800/80 shadow-xl rounded-2xl p-6 lg:p-8 border border-gray-100/40 dark:border-zinc-700/50 transition-all duration-300 relative overflow-hidden">
        <!-- Decorative elements -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-emerald-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-gradient-to-tr from-teal-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
        
        <div class="relative">
            <h2 class="text-2xl md:text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-500 to-teal-400">
                    PayPal Webhooks Configuration
                </span>
            </h2>
            <!-- Animated decorative element -->
            <div class="absolute -bottom-2 left-0 h-1 w-24 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full animate-pulse"></div>
        </div>
        <p class="mt-3 text-gray-600 dark:text-gray-400">
            Manage and verify your PayPal webhook settings for subscription events.
        </p>
    </div>

    <!-- Webhook Configuration Card -->
    <div class="backdrop-blur-sm bg-white/80 dark:bg-zinc-800/80 shadow-xl rounded-2xl p-6 lg:p-8 border border-gray-100/40 dark:border-zinc-700/50 transition-all duration-300 relative overflow-hidden">
        <!-- Decorative elements -->
        <div class="absolute top-0 -right-16 w-72 h-72 bg-gradient-to-bl from-teal-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
        <div class="absolute -bottom-16 left-0 w-72 h-72 bg-gradient-to-tr from-emerald-400/10 to-transparent rounded-full blur-3xl -z-10"></div>

        <h3 class="text-xl font-semibold leading-7 text-gray-900 dark:text-white mb-1">
            Webhook Settings
        </h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
            These are the critical webhook details for your application.
        </p>
            
        <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
            <!-- Webhook URL -->
            <div class="col-span-6">
                <label for="webhook-url" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-300">
                    Webhook URL
                </label>
                <div class="mt-2 flex rounded-xl shadow-sm">
                    <input 
                        type="text"
                        id="webhook-url"
                        value="{{ $webhookUrl }}"
                        class="block w-full flex-1 rounded-l-xl border-0 bg-gray-100 dark:bg-zinc-700/60 py-2.5 px-3.5 text-gray-900 dark:text-gray-200 ring-1 ring-inset ring-gray-300 dark:ring-zinc-600 focus:ring-2 focus:ring-inset focus:ring-emerald-500 sm:text-sm sm:leading-6 transition-colors duration-200"
                        readonly
                    >
                    <button
                        type="button"
                        x-data
                        x-on:click="navigator.clipboard.writeText('{{ $webhookUrl }}'); $dispatch('copied-url')"
                        class="relative -ml-px inline-flex items-center gap-x-1.5 rounded-r-xl px-3.5 py-2.5 text-sm font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-700/30 hover:bg-emerald-200 dark:hover:bg-emerald-700/50 ring-1 ring-inset ring-emerald-300 dark:ring-emerald-700/50 focus:z-10 transition-colors duration-200"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M7 9a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2V9z" />
                            <path d="M5 3a2 2 0 00-2 2v6a2 2 0 002 2V5h6a2 2 0 00-2-2H5z" />
                        </svg>
                        Copy
                    </button>
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    Use this URL in your PayPal webhook configuration.
                </p>
            </div>

            <!-- Webhook ID -->
            <div class="col-span-6 sm:col-span-4">
                <label for="webhook-id" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-300">
                    Webhook ID
                </label>
                <div class="mt-2">
                    <input 
                        type="text" 
                        id="webhook-id"
                        value="{{ $webhookId }}"
                        class="block w-full rounded-xl border-0 bg-gray-100 dark:bg-zinc-700/60 py-2.5 px-3.5 text-gray-900 dark:text-gray-200 ring-1 ring-inset ring-gray-300 dark:ring-zinc-600 focus:ring-2 focus:ring-inset focus:ring-emerald-500 sm:text-sm sm:leading-6 transition-colors duration-200"
                        readonly
                    >
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    This ID is configured in your <code>.env</code> file (<code>PAYPAL_WEBHOOK_ID</code>).
                </p>
            </div>
        </div>
    </div>

    <!-- Events Card -->
    <div class="backdrop-blur-sm bg-white/80 dark:bg-zinc-800/80 shadow-xl rounded-2xl p-6 lg:p-8 border border-gray-100/40 dark:border-zinc-700/50 transition-all duration-300 relative overflow-hidden">
        <!-- Decorative elements -->
        <div class="absolute -top-16 right-0 w-72 h-72 bg-gradient-to-bl from-emerald-400/5 to-transparent rounded-full blur-3xl -z-10"></div>
        <div class="absolute -bottom-16 -left-16 w-72 h-72 bg-gradient-to-tr from-teal-400/5 to-transparent rounded-full blur-3xl -z-10"></div>

        <h3 class="text-xl font-semibold leading-7 text-gray-900 dark:text-white mb-1">
            Required Webhook Events
        </h3>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 mb-6">
            Ensure these events are subscribed to in your PayPal webhook settings for proper functionality.
        </p>

        <div class="mt-6 flow-root">
            <ul role="list" class="-my-4 divide-y divide-gray-200 dark:divide-zinc-700/50">
                @forelse($events as $event => $description)
                    <li class="flex items-center justify-between py-4 hover:bg-gray-50/30 dark:hover:bg-zinc-700/20 transition-colors duration-150 -mx-4 px-4 rounded-lg">
                        <div class="flex flex-col">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $event }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $description }}
                            </p>
                        </div>
                        <div class="ml-4 flex-shrink-0">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30">
                                <svg class="mr-1.5 h-2 w-2 text-emerald-500 dark:text-emerald-400" fill="currentColor" viewBox="0 0 8 8">
                                    <circle cx="4" cy="4" r="3" />
                                </svg>
                                Required
                            </span>
                        </div>
                    </li>
                @empty
                    <li class="py-4 text-center text-gray-500 dark:text-gray-400">
                        No webhook events loaded.
                    </li>
                @endforelse
            </ul>
        </div>
        @if(session()->has('copied-url'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="fixed bottom-5 right-5 bg-emerald-500 text-white py-2 px-4 rounded-xl shadow-lg text-sm">
                Webhook URL copied to clipboard!
            </div>
        @endif
    </div>

    <!-- Setup Instructions Card -->
    <div class="backdrop-blur-sm bg-white/80 dark:bg-zinc-800/80 shadow-xl rounded-2xl p-6 lg:p-8 border border-gray-100/40 dark:border-zinc-700/50 transition-all duration-300 relative overflow-hidden">
        <!-- Decorative elements -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-teal-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-gradient-to-tr from-emerald-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
        
        <h3 class="text-xl font-semibold leading-7 text-gray-900 dark:text-white mb-1">
            Setup Instructions
        </h3>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 mb-6">
            Follow these steps to configure your PayPal webhook:
        </p>
            
        <div class="mt-6 prose prose-sm sm:prose-base dark:prose-invert max-w-none prose-headings:text-gray-800 dark:prose-headings:text-gray-100 prose-a:text-emerald-600 dark:prose-a:text-emerald-400 hover:prose-a:text-emerald-500 dark:hover:prose-a:text-emerald-300 prose-strong:text-gray-700 dark:prose-strong:text-gray-200 prose-code:bg-gray-100 dark:prose-code:bg-zinc-700 prose-code:p-1 prose-code:rounded-md prose-code:font-mono">
            <ol>
                <li>Go to your <a href="https://developer.paypal.com/dashboard/applications/live" target="_blank" rel="noopener noreferrer">PayPal Developer Dashboard</a>.</li>
                <li>Navigate to your application settings (usually under "My Apps & Credentials").</li>
                <li>Scroll down to the "Webhooks" section for your selected app.</li>
                <li>Click "Add Webhook".</li>
                <li>Paste the <strong>Webhook URL</strong> shown above into the corresponding field.</li>
                <li>For "Event types", select "All events" or individually select all the <strong>Required Webhook Events</strong> listed above. It's crucial all listed events are active.</li>
                <li>Click "Save".</li>
                <li>After saving, PayPal will display the Webhook ID. Copy this ID.</li>
                <li>Add or update the <code>PAYPAL_WEBHOOK_ID</code> variable in your application's <code>.env</code> file with this ID.</li>
            </ol>
            <p class="mt-4">
                <strong>Important:</strong> Ensure your application is publicly accessible for PayPal to send webhook notifications. During local development, you might need to use a tunneling service like ngrok.
            </p>
        </div>
    </div>
</div>

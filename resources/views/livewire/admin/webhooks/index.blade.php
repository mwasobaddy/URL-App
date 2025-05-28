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

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
            PayPal Webhooks Configuration
        </h2>
    </div>

    <!-- Webhook Configuration Card -->
    <div class="bg-white dark:bg-zinc-800 shadow-sm ring-1 ring-gray-900/5 dark:ring-zinc-700/5 rounded-xl">
        <div class="p-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                Webhook Settings
            </h3>
            
            <div class="mt-6 grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-6">
                <!-- Webhook URL -->
                <div class="col-span-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Webhook URL
                    </label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <input 
                            type="text" 
                            value="{{ $webhookUrl }}"
                            class="block w-full flex-1 rounded-md border-gray-300 dark:border-zinc-700 dark:bg-zinc-800 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"
                            readonly
                        >
                        <button
                            type="button"
                            x-data
                            x-on:click="navigator.clipboard.writeText('{{ $webhookUrl }}')"
                            class="ml-3 inline-flex items-center rounded-md border border-transparent bg-emerald-100 dark:bg-emerald-900/30 px-4 py-2 text-sm font-medium text-emerald-700 dark:text-emerald-300 hover:bg-emerald-200 dark:hover:bg-emerald-900/50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                        >
                            Copy
                        </button>
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Use this URL in your PayPal webhook configuration.
                    </p>
                </div>

                <!-- Webhook ID -->
                <div class="col-span-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Webhook ID
                    </label>
                    <div class="mt-1">
                        <input 
                            type="text" 
                            value="{{ $webhookId }}"
                            class="block w-full rounded-md border-gray-300 dark:border-zinc-700 dark:bg-zinc-800 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"
                            readonly
                        >
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        This ID is automatically configured in your .env file.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Events Card -->
    <div class="bg-white dark:bg-zinc-800 shadow-sm ring-1 ring-gray-900/5 dark:ring-zinc-700/5 rounded-xl">
        <div class="p-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                Required Webhook Events
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Make sure these events are enabled in your PayPal webhook configuration.
            </p>

            <div class="mt-6">
                <ul role="list" class="divide-y divide-gray-200 dark:divide-zinc-700">
                    @foreach($events as $event => $description)
                        <li class="py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex flex-col">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $event }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $description }}
                                    </p>
                                </div>
                                <div class="ml-4">
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 dark:bg-emerald-900/30 px-2.5 py-0.5 text-xs font-medium text-emerald-800 dark:text-emerald-300">
                                        Required
                                    </span>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <!-- Setup Instructions -->
    <div class="bg-white dark:bg-zinc-800 shadow-sm ring-1 ring-gray-900/5 dark:ring-zinc-700/5 rounded-xl">
        <div class="p-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                Setup Instructions
            </h3>
            
            <div class="mt-6 prose dark:prose-invert max-w-none">
                <ol class="space-y-4">
                    <li>Go to your <a href="https://developer.paypal.com/dashboard/applications/live" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-500" target="_blank">PayPal Developer Dashboard</a></li>
                    <li>Navigate to your app settings</li>
                    <li>Click on "Webhooks" in the left sidebar</li>
                    <li>Click "Add Webhook"</li>
                    <li>Enter the Webhook URL shown above</li>
                    <li>Select all the required events listed above</li>
                    <li>Click "Save"</li>
                    <li>Copy the generated Webhook ID and add it to your <code>.env</code> file as <code>PAYPAL_WEBHOOK_ID</code></li>
                </ol>
            </div>
        </div>
    </div>
</div>

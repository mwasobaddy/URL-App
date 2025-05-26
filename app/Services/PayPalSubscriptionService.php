<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Exception;

class PayPalSubscriptionService
{
    protected PayPalAPIService $paypalApi;
    protected SubscriptionService $subscriptionService;
    
    public function __construct(
        PayPalAPIService $paypalApi,
        SubscriptionService $subscriptionService
    ) {
        $this->paypalApi = $paypalApi;
        $this->subscriptionService = $subscriptionService;
    }
    protected PayPalClient $paypal;

    public function __construct()
    {
        $this->paypal = new PayPalClient;
        $this->paypal->setApiCredentials(config('paypal'));
        $this->paypal->getAccessToken();
    }

    public function createPlan(Plan $plan): string
    {
        $response = $this->paypal->createProduct([
            'name' => $plan->name,
            'description' => $plan->description,
            'type' => 'SERVICE',
            'category' => 'SOFTWARE',
        ]);

        if (!isset($response['id'])) {
            throw new \Exception('Failed to create PayPal product');
        }

        $productId = $response['id'];

        // Create Monthly Plan
        $monthlyPlan = $this->paypal->createPlan([
            'product_id' => $productId,
            'name' => "{$plan->name} (Monthly)",
            'description' => $plan->description,
            'status' => 'ACTIVE',
            'billing_cycles' => [
                [
                    'frequency' => [
                        'interval_unit' => 'MONTH',
                        'interval_count' => 1,
                    ],
                    'tenure_type' => 'REGULAR',
                    'sequence' => 1,
                    'total_cycles' => 0,
                    'pricing_scheme' => [
                        'fixed_price' => [
                            'value' => number_format($plan->monthly_price, 2, '.', ''),
                            'currency_code' => config('paypal.currency'),
                        ],
                    ],
                ],
            ],
            'payment_preferences' => [
                'auto_bill_outstanding' => true,
                'setup_fee' => [
                    'value' => '0',
                    'currency_code' => config('paypal.currency'),
                ],
                'setup_fee_failure_action' => 'CONTINUE',
                'payment_failure_threshold' => 3,
            ],
        ]);

        // Create Yearly Plan
        $yearlyPlan = $this->paypal->createPlan([
            'product_id' => $productId,
            'name' => "{$plan->name} (Yearly)",
            'description' => $plan->description,
            'status' => 'ACTIVE',
            'billing_cycles' => [
                [
                    'frequency' => [
                        'interval_unit' => 'YEAR',
                        'interval_count' => 1,
                    ],
                    'tenure_type' => 'REGULAR',
                    'sequence' => 1,
                    'total_cycles' => 0,
                    'pricing_scheme' => [
                        'fixed_price' => [
                            'value' => number_format($plan->yearly_price, 2, '.', ''),
                            'currency_code' => config('paypal.currency'),
                        ],
                    ],
                ],
            ],
            'payment_preferences' => [
                'auto_bill_outstanding' => true,
                'setup_fee' => [
                    'value' => '0',
                    'currency_code' => config('paypal.currency'),
                ],
                'setup_fee_failure_action' => 'CONTINUE',
                'payment_failure_threshold' => 3,
            ],
        ]);

        return [
            'monthly' => $monthlyPlan['id'] ?? null,
            'yearly' => $yearlyPlan['id'] ?? null,
        ];
    }

    public function createSubscription(User $user, Plan $plan, string $interval = 'monthly'): Subscription
    {
        $planId = $interval === 'yearly' ? $plan->paypal_yearly_plan_id : $plan->paypal_monthly_plan_id;
        
        $response = $this->paypal->createSubscription([
            'plan_id' => $planId,
            'subscriber' => [
                'name' => [
                    'given_name' => $user->name,
                ],
                'email_address' => $user->email,
            ],
            'application_context' => [
                'brand_name' => config('app.name'),
                'shipping_preference' => 'NO_SHIPPING',
                'user_action' => 'SUBSCRIBE_NOW',
                'return_url' => route('subscriptions.return'),
                'cancel_url' => route('subscriptions.cancel'),
            ],
        ]);

        if (!isset($response['id'])) {
            throw new \Exception('Failed to create PayPal subscription');
        }

        return Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'paypal_subscription_id' => $response['id'],
            'paypal_plan_id' => $planId,
            'status' => $response['status'],
            'interval' => $interval,
            'current_period_starts_at' => now(),
            'current_period_ends_at' => $interval === 'yearly' ? now()->addYear() : now()->addMonth(),
        ]);
    }

    public function cancelSubscription(Subscription $subscription): bool
    {
        $response = $this->paypal->cancelSubscription(
            $subscription->paypal_subscription_id,
            'Plan cancelled by user'
        );

        if ($response === '') {
            $subscription->cancel();
            return true;
        }

        return false;
    }

    public function handleWebhook(array $payload)
    {
        $eventType = $payload['event_type'] ?? null;
        $resource = $payload['resource'] ?? [];

        switch ($eventType) {
            case 'BILLING.SUBSCRIPTION.CREATED':
                // Handle subscription creation
                break;
            case 'BILLING.SUBSCRIPTION.CANCELLED':
                // Handle subscription cancellation
                break;
            case 'BILLING.SUBSCRIPTION.SUSPENDED':
                // Handle subscription suspension
                break;
            case 'BILLING.SUBSCRIPTION.PAYMENT.FAILED':
                // Handle payment failure
                break;
            case 'BILLING.SUBSCRIPTION.RENEWED':
                // Handle subscription renewal
                break;
        }
    }
}

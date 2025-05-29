<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Exception;

class PayPalSubscriptionService
{
    protected PayPalClient $paypal;
    protected PayPalAPIService $paypalApi;
    protected SubscriptionService $subscriptionService;
    
    public function __construct(
        PayPalAPIService $paypalApi,
        SubscriptionService $subscriptionService
    ) {
        $this->paypalApi = $paypalApi;
        $this->subscriptionService = $subscriptionService;
        $this->paypal = new PayPalClient(config('paypal'));
        $this->paypal->getAccessToken();
    }

    public function createPlan(Plan $plan, PlanVersion $version): array
    {
        // Create product first
        $response = $this->paypal->createProduct([
            'name' => $version->name,
            'description' => $version->description,
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
            'name' => "{$version->name} (Monthly)",
            'description' => $version->description,
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
                            'value' => number_format($version->monthly_price, 2, '.', ''),
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
            'name' => "{$version->name} (Yearly)",
            'description' => $version->description,
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
                            'value' => number_format($version->yearly_price, 2, '.', ''),
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
        $version = $plan->getCurrentVersion();
        if (!$version) {
            throw new \Exception('No active version found for the plan');
        }

        $planId = $interval === 'yearly' ? $version->paypal_yearly_plan_id : $version->paypal_monthly_plan_id;
        if (!$planId) {
            throw new \Exception('PayPal plan ID not found');
        }

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
            'plan_version_id' => $version->id,
            'paypal_subscription_id' => $response['id'],
            'paypal_plan_id' => $planId,
            'status' => $response['status'],
            'interval' => $interval,
            'current_period_starts_at' => now(),
            'current_period_ends_at' => $interval === 'yearly' ? now()->addYear() : now()->addMonth(),
        ]);
    }

    public function updateSubscriptionPlan(string $subscriptionId, string $newPlanId): bool
    {
        $response = $this->paypal->updateSubscription($subscriptionId, [
            'plan_id' => $newPlanId,
        ]);

        return isset($response['id']);
    }

    public function updateSubscriptionPricing(
        string $subscriptionId,
        PlanVersion $newVersion,
        string $interval,
        array $proration
    ): bool {
        $price = $interval === 'yearly' ? $newVersion->yearly_price : $newVersion->monthly_price;

        $response = $this->paypal->updateSubscriptionPricing($subscriptionId, [
            'pricing_scheme' => [
                'fixed_price' => [
                    'value' => number_format($price, 2, '.', ''),
                    'currency_code' => config('paypal.currency'),
                ],
            ],
            'billing_info' => [
                'outstanding_balance' => [
                    'value' => number_format($proration['net_amount'], 2, '.', ''),
                    'currency_code' => config('paypal.currency'),
                ],
            ],
        ]);

        return isset($response['id']);
    }

    public function cancelSubscription(Subscription $subscription): bool
    {
        return $this->paypal->cancelSubscription(
            $subscription->paypal_subscription_id,
            'Plan cancelled by user'
        );
    }

    public function verifyWebhookSignature(Request $request): bool
    {
        return $this->paypalApi->verifyWebhookSignature($request);
    }

    public function handleWebhook(array $payload): void
    {
        // Extract subscription ID and event type
        $subscriptionId = $payload['resource']['id'] ?? null;
        $eventType = $payload['event_type'] ?? null;
        
        if (!$subscriptionId || !$eventType) {
            return;
        }
        
        // Find the subscription
        $subscription = Subscription::where('paypal_subscription_id', $subscriptionId)->first();
        if (!$subscription) {
            return;
        }
        
        switch ($eventType) {
            case 'BILLING.SUBSCRIPTION.RENEWED':
                // Update subscription renewal dates
                $subscription->update([
                    'current_period_starts_at' => now(),
                    'current_period_ends_at' => $subscription->interval === 'yearly' 
                        ? now()->addYear() 
                        : now()->addMonth()
                ]);
                
                // Send renewal completed notification
                $this->subscriptionService->sendRenewalCompletedNotification($subscription);
                break;
                
            case 'BILLING.SUBSCRIPTION.CANCELLED':
                $subscription->cancel();
                break;
                
            case 'BILLING.SUBSCRIPTION.PAYMENT.FAILED':
                // Update subscription status
                $subscription->update(['status' => 'payment_failed']);
                break;
        }
    }
}

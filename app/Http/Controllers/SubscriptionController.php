<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Services\PayPalSubscriptionService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    protected PayPalSubscriptionService $paypalService;

    public function __construct(PayPalSubscriptionService $paypalService)
    {
        $this->paypalService = $paypalService;
    }

    public function return(Request $request)
    {
        try {
            $subscription = Subscription::where('paypal_subscription_id', $request->subscription_id)->firstOrFail();
            
            // Verify the subscription status with PayPal
            $paypalSubscription = $this->paypalService->getSubscription($request->subscription_id);
            
            if ($paypalSubscription['status'] === 'ACTIVE') {
                $subscription->update([
                    'status' => 'active',
                    'current_period_starts_at' => now(),
                    'current_period_ends_at' => $subscription->interval === 'yearly' ? now()->addYear() : now()->addMonth(),
                ]);

                return redirect()->route('subscription.manage')
                    ->with('success', 'Your subscription has been activated successfully!');
            }

            return redirect()->route('subscription.manage')
                ->with('error', 'There was a problem activating your subscription. Please try again.');
        } catch (\Exception $e) {
            return redirect()->route('subscription.manage')
                ->with('error', 'There was a problem activating your subscription. Please try again.');
        }
    }

    public function cancel()
    {
        return redirect()->route('subscription.manage')
            ->with('error', 'The subscription process was cancelled.');
    }

    public function webhook(Request $request)
    {
        try {
            // Verify webhook signature
            if (!$this->paypalService->verifyWebhookSignature($request)) {
                return response()->json(['error' => 'Invalid signature'], 400);
            }

            $this->paypalService->handleWebhook($request->all());

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

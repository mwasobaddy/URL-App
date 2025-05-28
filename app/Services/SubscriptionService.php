<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Exception;

class SubscriptionService
{
    protected RoleCheckService $roleService;
    protected ?PayPalSubscriptionService $paypalService;
    protected Collection $featureLimits;
    
    public function __construct(
        RoleCheckService $roleService,
        ?PayPalSubscriptionService $paypalService = null
    ) {
        $this->roleService = $roleService;
        $this->paypalService = $paypalService;
        $this->featureLimits = collect([
            'free' => [
                'lists' => 3,
                'urls_per_list' => 10,
                'collaborators' => 0,
                'custom_domains' => false,
                'analytics' => false,
            ],
            'premium' => [
                'lists' => -1, // unlimited
                'urls_per_list' => -1, // unlimited
                'collaborators' => -1, // unlimited
                'custom_domains' => true,
                'analytics' => true,
            ],
        ]);
    }
    
    /**
     * Get the subscription state for a user
     */
    public function setPayPalSubscriptionService(PayPalSubscriptionService $paypalService): void
    {
        $this->paypalService = $paypalService;
    }
    
    /**
     * Get the subscription state for a user
     */
    public function getSubscriptionState(User $user): array
    {
        $subscription = $user->subscription;
        
        if (!$subscription) {
            return [
                'status' => 'none',
                'plan' => null,
                'trial_ends_at' => null,
                'ends_at' => null,
            ];
        }
        
        return [
            'status' => $this->getStatus($subscription),
            'plan' => $subscription->plan,
            'trial_ends_at' => $subscription->trial_ends_at,
            'ends_at' => $subscription->ends_at,
            'version' => $subscription->planVersion,
        ];
    }
    
    /**
     * Get feature limits for a user based on their subscription
     */
    public function getFeatureLimits(User $user): array
    {
        $subscription = $user->subscription;
        
        if (!$subscription || !$subscription->planVersion) {
            return $this->featureLimits['free'];
        }

        $version = $subscription->planVersion;
        return array_merge(
            $this->featureLimits['free'],
            $version->features
        );
    }
    
    /**
     * Switch a subscription to a new plan version
     */
    public function switchPlanVersion(Subscription $subscription, PlanVersion $newVersion): bool
    {
        try {
            // Calculate proration
            $currentVersion = $subscription->planVersion;
            if ($currentVersion) {
                $proration = $currentVersion->calculateProration($newVersion, $subscription->interval);
                
                // Update PayPal subscription if needed
                if ($proration['net_amount'] != 0) {
                    $this->paypalService->updateSubscriptionPricing(
                        $subscription->paypal_subscription_id,
                        $newVersion,
                        $subscription->interval,
                        $proration
                    );
                }
            }
            
            // Update PayPal plan ID
            $planId = $subscription->interval === 'yearly' 
                ? $newVersion->paypal_yearly_plan_id
                : $newVersion->paypal_monthly_plan_id;
                
            $this->paypalService->updateSubscriptionPlan(
                $subscription->paypal_subscription_id,
                $planId
            );
            
            // Update local subscription
            $subscription->update([
                'plan_version_id' => $newVersion->id,
                'paypal_plan_id' => $planId,
            ]);
            
            return true;
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }
    
    /**
     * Check if a user has access to a feature
     */
    public function hasFeatureAccess(User $user, string $feature): bool
    {
        $limits = $this->getFeatureLimits($user);
        return isset($limits[$feature]) && $limits[$feature] !== false;
    }
    
    /**
     * Check if a user has reached their limit for a specific feature
     */
    public function hasReachedLimit(User $user, string $feature, int $currentCount): bool
    {
        $limits = $this->getFeatureLimits($user);
        $limit = $limits[$feature] ?? 0;
        
        // -1 indicates unlimited
        if ($limit === -1) {
            return false;
        }
        
        return $currentCount >= $limit;
    }
    
    /**
     * Get the remaining quota for a feature
     */
    public function getRemainingQuota(User $user, string $feature, int $currentCount): ?int
    {
        $limits = $this->getFeatureLimits($user);
        $limit = $limits[$feature] ?? 0;
        
        if ($limit === -1) {
            return null; // unlimited
        }
        
        return max(0, $limit - $currentCount);
    }
    
    /**
     * Get subscription status
     */
    private function getStatus(Subscription $subscription): string
    {
        if ($subscription->cancelled_at && $subscription->ends_at > now()) {
            return 'cancelled_pending';
        }
        
        if ($subscription->ends_at && $subscription->ends_at <= now()) {
            return 'expired';
        }
        
        if ($subscription->trial_ends_at && $subscription->trial_ends_at > now()) {
            return 'trialing';
        }
        
        if ($subscription->active) {
            return 'active';
        }
        
        return 'inactive';
    }
    
    /**
     * Check if subscription is in trial period
     */
    public function onTrial(User $user): bool
    {
        $subscription = $user->subscription;
        return $subscription && 
               $subscription->trial_ends_at && 
               $subscription->trial_ends_at->isFuture();
    }
    
    /**
     * Check if subscription has expired
     */
    public function hasExpired(User $user): bool
    {
        $subscription = $user->subscription;
        return $subscription && 
               $subscription->ends_at && 
               $subscription->ends_at->isPast();
    }
    
    /**
     * Get days until subscription expires
     */
    public function daysUntilExpiration(User $user): ?int
    {
        $subscription = $user->subscription;
        if (!$subscription || !$subscription->ends_at) {
            return null;
        }
        
        return now()->diffInDays($subscription->ends_at, false);
    }
    
    /**
     * Send an upcoming renewal notification for a subscription
     */
    public function sendUpcomingRenewalNotification(Subscription $subscription): void
    {
        if (!$subscription->isActive()) {
            return;
        }

        $daysUntilRenewal = now()->diffInDays($subscription->current_period_ends_at);
        
        // Send notifications 7 days and 1 day before renewal
        if (in_array($daysUntilRenewal, [7, 1])) {
            $subscription->user->notify(new \App\Notifications\SubscriptionRenewalNotification(
                $subscription,
                'upcoming'
            ));
        }
    }

    /**
     * Send a renewal completed notification for a subscription
     */
    public function sendRenewalCompletedNotification(Subscription $subscription): void
    {
        if (!$subscription->isActive()) {
            return;
        }

        $subscription->user->notify(new \App\Notifications\SubscriptionRenewalNotification(
            $subscription,
            'completed'
        ));
    }

    /**
     * Check for subscriptions that need renewal notifications
     * This should be called via a daily scheduled task
     */
    public function checkAndSendRenewalNotifications(): void
    {
        Subscription::query()
            ->where('status', 'active')
            ->where('cancelled_at', null)
            ->whereNotNull('current_period_ends_at')
            ->chunk(100, function ($subscriptions) {
                foreach ($subscriptions as $subscription) {
                    $this->sendUpcomingRenewalNotification($subscription);
                }
            });
    }
}

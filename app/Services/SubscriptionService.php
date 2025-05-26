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
    protected PayPalSubscriptionService $paypalService;
    protected Collection $featureLimits;
    
    public function __construct(RoleCheckService $roleService)
    {
        $this->roleService = $roleService;
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
        ];
    }
    
    /**
     * Get feature limits for a user based on their subscription
     */
    public function getFeatureLimits(User $user): array
    {
        $role = $this->roleService->getHighestRole() ?? 'free';
        return $this->featureLimits->get($role, $this->featureLimits['free']);
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
}

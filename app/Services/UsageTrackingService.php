<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class UsageTrackingService
{
    protected $cachePrefix = 'usage_tracking:';
    protected $cacheDuration = 86400; // 24 hours in seconds
    
    /**
     * Track usage of a feature
     */
    public function trackUsage(User $user, string $feature, int $amount = 1): void
    {
        $key = $this->getCacheKey($user->id, $feature, now()->format('Y-m'));
        $currentUsage = Cache::get($key, 0);
        Cache::put($key, $currentUsage + $amount, Carbon::now()->addDays(90));
    }
    
    /**
     * Get current usage for a feature
     */
    public function getCurrentUsage(User $user, string $feature): int
    {
        $key = $this->getCacheKey($user->id, $feature, now()->format('Y-m'));
        return Cache::get($key, 0);
    }
    
    /**
     * Get usage history for a feature
     */
    public function getUsageHistory(User $user, string $feature, int $months = 3): array
    {
        $history = [];
        $currentDate = now();
        
        for ($i = 0; $i < $months; $i++) {
            $date = $currentDate->copy()->subMonths($i);
            $key = $this->getCacheKey($user->id, $feature, $date->format('Y-m'));
            $history[$date->format('Y-m')] = Cache::get($key, 0);
        }
        
        return $history;
    }
    
    /**
     * Reset usage tracking for a feature
     */
    public function resetUsage(User $user, string $feature): void
    {
        $key = $this->getCacheKey($user->id, $feature, now()->format('Y-m'));
        Cache::forget($key);
    }
    
    /**
     * Get total usage across all tracked features
     */
    public function getTotalUsage(User $user): array
    {
        $features = ['lists', 'urls', 'collaborators'];
        $usage = [];
        
        foreach ($features as $feature) {
            $usage[$feature] = $this->getCurrentUsage($user, $feature);
        }
        
        return $usage;
    }
    
    /**
     * Generate cache key for usage tracking
     */
    protected function getCacheKey(int $userId, string $feature, string $period): string
    {
        return "{$this->cachePrefix}{$userId}:{$feature}:{$period}";
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SystemMonitoringService
{
    /**
     * Cache keys for monitoring data
     */
    const CACHE_HITS_KEY = 'cache_hits';
    const CACHE_MISSES_KEY = 'cache_misses';
    const CACHE_KEYS_KEY = 'total_cache_keys';
    const PROCESSING_JOBS_KEY = 'processing_jobs';

    /**
     * Increment cache hit counter
     */
    public function incrementCacheHits(): void
    {
        Cache::increment(self::CACHE_HITS_KEY);
    }

    /**
     * Increment cache miss counter
     */
    public function incrementCacheMisses(): void
    {
        Cache::increment(self::CACHE_MISSES_KEY);
    }

    /**
     * Update total cache keys count
     */
    public function updateCacheKeysCount(): void
    {
        $count = match(config('cache.default')) {
            'redis' => $this->countRedisKeys(),
            'file' => $this->countFileKeys(),
            default => 0,
        };
        
        Cache::put(self::CACHE_KEYS_KEY, $count);
    }

    /**
     * Count Redis cache keys
     */
    protected function countRedisKeys(): int
    {
        try {
            $redis = Redis::connection();
            return $redis->dbsize();
        } catch (\Exception $e) {
            Log::error('Failed to count Redis keys: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Count file cache keys
     */
    protected function countFileKeys(): int
    {
        try {
            $path = storage_path('framework/cache/data');
            $files = glob($path . '/*');
            return count($files);
        } catch (\Exception $e) {
            Log::error('Failed to count cache files: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Track processing jobs
     */
    public function trackProcessingJob(bool $increment = true): void
    {
        if ($increment) {
            Cache::increment(self::PROCESSING_JOBS_KEY);
        } else {
            Cache::decrement(self::PROCESSING_JOBS_KEY);
        }
    }

    /**
     * Reset daily counters
     */
    public function resetDailyCounters(): void
    {
        Cache::put(self::CACHE_HITS_KEY, 0);
        Cache::put(self::CACHE_MISSES_KEY, 0);
        Cache::put(self::PROCESSING_JOBS_KEY, 0);
        $this->updateCacheKeysCount();
    }

    /**
     * Get disk usage percentage
     */
    public function getDiskUsagePercentage(): float
    {
        $disk = disk_free_space('/');
        $total = disk_total_space('/');
        return round(($total - $disk) / $total * 100, 2);
    }

    /**
     * Get memory usage percentage
     */
    public function getMemoryUsagePercentage(): float
    {
        $used = memory_get_usage(true);
        $limit = $this->getMemoryLimitInBytes();
        return round(($used / $limit) * 100, 2);
    }

    /**
     * Convert memory limit to bytes
     */
    protected function getMemoryLimitInBytes(): int
    {
        $limit = ini_get('memory_limit');
        
        if ($limit === '-1') {
            return PHP_INT_MAX;
        }
        
        $value = (int) $limit;
        
        return match(strtoupper(substr($limit, -1))) {
            'G' => $value * 1024 * 1024 * 1024,
            'M' => $value * 1024 * 1024,
            'K' => $value * 1024,
            default => $value,
        };
    }

    /**
     * Get queue health status
     */
    public function getQueueHealthStatus(): array
    {
        return [
            'pending' => Queue::size('default'),
            'failed' => DB::table('failed_jobs')->count(),
            'processing' => Cache::get(self::PROCESSING_JOBS_KEY, 0),
        ];
    }

    /**
     * Check if system resources are within healthy limits
     */
    public function checkSystemHealth(): array
    {
        $cpuLoad = sys_getloadavg()[0];
        $memoryUsage = $this->getMemoryUsagePercentage();
        $diskUsage = $this->getDiskUsagePercentage();
        
        return [
            'cpu' => [
                'status' => $cpuLoad > 2.0 ? 'critical' : ($cpuLoad > 1.0 ? 'warning' : 'healthy'),
                'value' => $cpuLoad
            ],
            'memory' => [
                'status' => $memoryUsage > 90 ? 'critical' : ($memoryUsage > 75 ? 'warning' : 'healthy'),
                'value' => $memoryUsage
            ],
            'disk' => [
                'status' => $diskUsage > 90 ? 'critical' : ($diskUsage > 75 ? 'warning' : 'healthy'),
                'value' => $diskUsage
            ]
        ];
    }
}

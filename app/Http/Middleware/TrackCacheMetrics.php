<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\SystemMonitoringService;
use Illuminate\Support\Facades\Cache;

class TrackCacheMetrics
{
    protected $monitoringService;

    public function __construct(SystemMonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
    }

    public function handle(Request $request, Closure $next)
    {
        // Track original has method
        $originalHas = Cache::has(...);
        
        // Override has method to track cache hits/misses
        Cache::macro('has', function ($key) use ($originalHas) {
            $hasKey = $originalHas($key);
            
            if ($hasKey) {
                $this->monitoringService->incrementCacheHits();
            } else {
                $this->monitoringService->incrementCacheMisses();
            }
            
            return $hasKey;
        });

        return $next($request);
    }
}

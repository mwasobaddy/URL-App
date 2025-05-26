<?php

use function Livewire\Volt\{state, computed, mount};
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

state([
    'refreshInterval' => 30, // seconds
    'systemMetrics' => [],
    'queueMetrics' => [],
    'cacheMetrics' => [],
    'errorMetrics' => [],
    'loading' => false,
]);

// Initialize monitoring
$mount = function () {
    $this->refreshMetrics();
};

// Refresh all metrics
$refreshMetrics = function () {
    $this->loading = true;
    
    $this->systemMetrics = $this->getSystemMetrics();
    $this->queueMetrics = $this->getQueueMetrics();
    $this->cacheMetrics = $this->getCacheMetrics();
    $this->errorMetrics = $this->getErrorMetrics();
    
    $this->loading = false;
};

// System metrics collection
$getSystemMetrics = function () {
    $load = sys_getloadavg();
    $memory = memory_get_usage(true);
    $disk = disk_free_space('/');
    
    return [
        'cpu_load' => [
            '1m' => round($load[0], 2),
            '5m' => round($load[1], 2),
            '15m' => round($load[2], 2),
        ],
        'memory_usage' => [
            'used' => round($memory / 1024 / 1024, 2), // MB
            'limit' => ini_get('memory_limit'),
        ],
        'disk_space' => [
            'free' => round($disk / 1024 / 1024 / 1024, 2), // GB
            'total' => round(disk_total_space('/') / 1024 / 1024 / 1024, 2), // GB
        ],
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version(),
        'server_time' => now()->format('Y-m-d H:i:s'),
    ];
};

// Queue metrics collection
$getQueueMetrics = function () {
    $defaultQueue = config('queue.default');
    $queueSize = Queue::size('default');
    
    $failedJobs = DB::table('failed_jobs')
        ->whereBetween('failed_at', [now()->subDay(), now()])
        ->count();
    
    $processingJobs = Cache::get('processing_jobs', 0);
    
    return [
        'driver' => $defaultQueue,
        'pending_jobs' => $queueSize,
        'failed_jobs_24h' => $failedJobs,
        'processing_jobs' => $processingJobs,
        'health_status' => $failedJobs > 50 ? 'critical' : ($failedJobs > 20 ? 'warning' : 'healthy'),
    ];
};

// Cache metrics collection
$getCacheMetrics = function () {
    $driver = config('cache.default');
    $hits = Cache::get('cache_hits', 0);
    $misses = Cache::get('cache_misses', 0);
    $ratio = $hits + $misses > 0 ? round(($hits / ($hits + $misses)) * 100, 2) : 0;
    
    return [
        'driver' => $driver,
        'hits' => $hits,
        'misses' => $misses,
        'hit_ratio' => $ratio,
        'total_keys' => Cache::get('total_cache_keys', 0),
        'health_status' => $ratio < 50 ? 'warning' : 'healthy',
    ];
};

// Error metrics collection
$getErrorMetrics = function () {
    $today = now()->format('Y-m-d');
    $logPath = storage_path("logs/laravel-{$today}.log");
    
    if (!file_exists($logPath)) {
        return [
            'error_count_24h' => 0,
            'warning_count_24h' => 0,
            'last_error' => null,
            'health_status' => 'healthy',
        ];
    }
    
    $errorCount = 0;
    $warningCount = 0;
    $lastError = null;
    
    // Read last 1000 lines of log file
    $lines = array_slice(file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES), -1000);
    
    foreach ($lines as $line) {
        if (str_contains($line, '.ERROR')) {
            $errorCount++;
            if (!$lastError) {
                $lastError = $line;
            }
        } elseif (str_contains($line, '.WARNING')) {
            $warningCount++;
        }
    }
    
    return [
        'error_count_24h' => $errorCount,
        'warning_count_24h' => $warningCount,
        'last_error' => $lastError,
        'health_status' => $errorCount > 50 ? 'critical' : ($errorCount > 20 ? 'warning' : 'healthy'),
    ];
};

?>

<div>
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:p-6">
            <div class="sm:flex sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        System Health Dashboard
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Monitor system performance and health metrics
                    </p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <button 
                        type="button"
                        wire:click="refreshMetrics"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
                    >
                        <svg wire:loading wire:target="refreshMetrics" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="refreshMetrics">
                            Refresh Metrics
                        </span>
                        <span wire:loading wire:target="refreshMetrics">
                            Refreshing...
                        </span>
                    </button>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Server Status -->
                <div class="bg-white dark:bg-zinc-900 overflow-hidden rounded-lg border border-gray-200 dark:border-zinc-700">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 01-3-3m3 3a3 3 0 100 6h13.5a3 3 0 100-6m-16.5-3a3 3 0 013-3h13.5a3 3 0 013 3m-19.5 0a4.5 4.5 0 01.9-2.7L5.737 5.1a3.375 3.375 0 012.7-1.35h7.126c1.062 0 2.062.5 2.7 1.35l2.587 3.45a4.5 4.5 0 01.9 2.7m0 0a3 3 0 01-3 3m0 3h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008zm-3 6h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        CPU Load Average
                                    </dt>
                                    <dd>
                                        <div class="text-lg font-medium text-gray-900 dark:text-white">
                                            {{ $systemMetrics['cpu_load']['1m'] }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            5m: {{ $systemMetrics['cpu_load']['5m'] }} | 15m: {{ $systemMetrics['cpu_load']['15m'] }}
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Queue Health -->
                <div class="bg-white dark:bg-zinc-900 overflow-hidden rounded-lg border border-gray-200 dark:border-zinc-700">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        Queue Status
                                    </dt>
                                    <dd>
                                        <div class="text-lg font-medium text-gray-900 dark:text-white">
                                            {{ $queueMetrics['pending_jobs'] }} pending
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $queueMetrics['failed_jobs_24h'] }} failed in 24h
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cache Performance -->
                <div class="bg-white dark:bg-zinc-900 overflow-hidden rounded-lg border border-gray-200 dark:border-zinc-700">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25M9 16.5v.75m3-3v3M15 12v5.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        Cache Hit Ratio
                                    </dt>
                                    <dd>
                                        <div class="text-lg font-medium text-gray-900 dark:text-white">
                                            {{ $cacheMetrics['hit_ratio'] }}%
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ number_format($cacheMetrics['hits']) }} hits / {{ number_format($cacheMetrics['misses']) }} misses
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error Rate -->
                <div class="bg-white dark:bg-zinc-900 overflow-hidden rounded-lg border border-gray-200 dark:border-zinc-700">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        Error Rate (24h)
                                    </dt>
                                    <dd>
                                        <div class="text-lg font-medium text-gray-900 dark:text-white">
                                            {{ $errorMetrics['error_count_24h'] }} errors
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $errorMetrics['warning_count_24h'] }} warnings
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Metrics -->
            <div class="mt-8 grid grid-cols-1 gap-6">
                <!-- System Information -->
                <div class="bg-white dark:bg-zinc-900 shadow overflow-hidden rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">System Information</h4>
                        <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Memory Usage</dt>
                                <dd class="mt-1">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $systemMetrics['memory_usage']['used'] }}MB / {{ $systemMetrics['memory_usage']['limit'] }}
                                    </div>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Disk Space</dt>
                                <dd class="mt-1">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $systemMetrics['disk_space']['free'] }}GB free of {{ $systemMetrics['disk_space']['total'] }}GB
                                    </div>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">PHP Version</dt>
                                <dd class="mt-1">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $systemMetrics['php_version'] }}
                                    </div>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Laravel Version</dt>
                                <dd class="mt-1">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $systemMetrics['laravel_version'] }}
                                    </div>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Queue Details -->
                <div class="bg-white dark:bg-zinc-900 shadow overflow-hidden rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Queue Health Status</h4>
                        <dl class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Queue Driver</dt>
                                <dd class="mt-1">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $queueMetrics['driver'] }}
                                    </div>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Processing Jobs</dt>
                                <dd class="mt-1">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $queueMetrics['processing_jobs'] }}
                                    </div>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Health Status</dt>
                                <dd class="mt-1">
                                    <span @class([
                                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                        'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' => $queueMetrics['health_status'] === 'healthy',
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300' => $queueMetrics['health_status'] === 'warning',
                                        'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' => $queueMetrics['health_status'] === 'critical',
                                    ])>
                                        {{ ucfirst($queueMetrics['health_status']) }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Cache Details -->
                <div class="bg-white dark:bg-zinc-900 shadow overflow-hidden rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Cache Performance</h4>
                        <dl class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Cache Driver</dt>
                                <dd class="mt-1">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $cacheMetrics['driver'] }}
                                    </div>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Keys</dt>
                                <dd class="mt-1">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ number_format($cacheMetrics['total_keys']) }}
                                    </div>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Health Status</dt>
                                <dd class="mt-1">
                                    <span @class([
                                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                        'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' => $cacheMetrics['health_status'] === 'healthy',
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300' => $cacheMetrics['health_status'] === 'warning',
                                        'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' => $cacheMetrics['health_status'] === 'critical',
                                    ])>
                                        {{ ucfirst($cacheMetrics['health_status']) }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Error Details -->
                @if($errorMetrics['last_error'])
                <div class="bg-white dark:bg-zinc-900 shadow overflow-hidden rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Latest Error</h4>
                        <div class="bg-gray-50 dark:bg-zinc-800 rounded p-4">
                            <pre class="text-sm text-gray-900 dark:text-white overflow-x-auto">{{ $errorMetrics['last_error'] }}</pre>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Auto-refresh -->
    <div x-data="{
        timer: null,
        startTimer() {
            this.timer = setInterval(() => {
                @this.refreshMetrics()
            }, {{ $refreshInterval * 1000 }})
        },
        stopTimer() {
            clearInterval(this.timer)
        }
    }" x-init="startTimer" @disconnect.window="stopTimer">
    </div>
</div>

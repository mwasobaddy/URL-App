<?php

use function Livewire\Volt\{state, computed, mount};
use App\Models\Subscription;
use App\Models\Plan;
use App\Models\PlanVersion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

state([
    'dateRange' => '30', // days
    'period' => 'daily', // daily, weekly, monthly
    'loading' => false,
    'exportFormat' => 'csv',
    'planFilter' => 'all'
]);

$getRevenueMetrics = function() {
    $endDate = now();
    $startDate = now()->subDays($this->dateRange);
    
    // Base query for all subscription payments
    $query = Subscription::query()
        ->with(['plan', 'planVersion'])
        ->where('status', 'active')
        ->whereBetween('created_at', [$startDate, $endDate]);

    // Revenue by plan type
    $revenueByPlan = $query->get()
        ->groupBy('plan.name')
        ->map(function ($subscriptions, $planName) {
            return [
                'plan' => $planName,
                'revenue' => $subscriptions->sum(function ($subscription) {
                    return $subscription->interval === 'yearly'
                        ? $subscription->planVersion->yearly_price
                        : $subscription->planVersion->monthly_price;
                }),
                'count' => $subscriptions->count()
            ];
        });

    // Revenue by period
    $periodQuery = match($this->period) {
        'weekly' => DB::raw('YEARWEEK(created_at) as period'),
        'monthly' => DB::raw('DATE_FORMAT(created_at, "%Y-%m") as period'),
        default => DB::raw('DATE(created_at) as period'), // daily
    };

    $revenueByPeriod = $query->select(
            $periodQuery,
            DB::raw('COUNT(*) as subscriptions'),
            DB::raw('SUM(CASE 
                WHEN interval = "yearly" THEN plan_versions.yearly_price
                ELSE plan_versions.monthly_price 
                END) as revenue')
        )
        ->join('plan_versions', 'subscriptions.plan_version_id', '=', 'plan_versions.id')
        ->groupBy('period')
        ->orderBy('period')
        ->get();

    // Payment success/failure rates
    $totalPayments = $query->count();
    $failedPayments = $query->where('status', 'payment_failed')->count();
    $successRate = $totalPayments > 0 ? (($totalPayments - $failedPayments) / $totalPayments) * 100 : 0;

    // Refund tracking
    $refunds = $query->where('status', 'refunded')->get();
    $refundAmount = $refunds->sum(function ($subscription) {
        return $subscription->interval === 'yearly'
            ? $subscription->planVersion->yearly_price
            : $subscription->planVersion->monthly_price;
    });

    return [
        'revenue_by_plan' => $revenueByPlan,
        'revenue_by_period' => $revenueByPeriod,
        'total_revenue' => $revenueByPlan->sum('revenue'),
        'total_subscriptions' => $revenueByPlan->sum('count'),
        'payment_success_rate' => round($successRate, 2),
        'failed_payments' => $failedPayments,
        'refund_amount' => round($refundAmount, 2),
        'refund_count' => $refunds->count(),
    ];
};

$metrics = computed(function () {
    return $this->getRevenueMetrics();
});

$updateDateRange = function ($days) {
    $this->dateRange = $days;
};

$updatePeriod = function ($period) {
    $this->period = $period;
};

$exportReport = function () {
    $this->loading = true;
    
    try {
        $metrics = $this->metrics;
        $filename = 'revenue-report-' . now()->format('Y-m-d') . '.' . $this->exportFormat;
        
        if ($this->exportFormat === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ];
            
            $callback = function() use ($metrics) {
                $file = fopen('php://output', 'w');
                
                // Revenue by Plan
                fputcsv($file, ['Revenue by Plan']);
                fputcsv($file, ['Plan', 'Revenue', 'Subscriptions']);
                foreach ($metrics['revenue_by_plan'] as $plan) {
                    fputcsv($file, [$plan['plan'], $plan['revenue'], $plan['count']]);
                }
                
                fputcsv($file, []); // Empty line
                
                // Revenue by Period
                fputcsv($file, ['Revenue by Period']);
                fputcsv($file, ['Period', 'Revenue', 'Subscriptions']);
                foreach ($metrics['revenue_by_period'] as $period) {
                    fputcsv($file, [$period->period, $period->revenue, $period->subscriptions]);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
        }
        
        // Add support for other export formats here
        
    } catch (\Exception $e) {
        $this->addError('export', 'Failed to export report. Please try again.');
    }
    
    $this->loading = false;
};

?>

<div>
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:p-6">
            <div class="sm:flex sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Revenue Analytics
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Track revenue metrics and payment performance
                    </p>
                </div>
                <div class="mt-4 sm:mt-0 sm:flex sm:space-x-3">
                    <!-- Date Range Selection -->
                    <div class="inline-flex rounded-md shadow-sm">
                        <button 
                            type="button" 
                            @class([
                                'relative inline-flex items-center px-4 py-2 rounded-l-md border text-sm font-medium focus:z-10 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500',
                                'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-900/50 dark:border-emerald-700 dark:text-emerald-300' => $dateRange === '7',
                                'bg-white border-gray-300 text-gray-700 hover:bg-gray-50 dark:bg-zinc-800 dark:border-zinc-600 dark:text-gray-300 dark:hover:bg-zinc-700' => $dateRange !== '7'
                            ])
                            wire:click="updateDateRange('7')"
                        >
                            7 days
                        </button>
                        <button 
                            type="button" 
                            @class([
                                'relative -ml-px inline-flex items-center px-4 py-2 border text-sm font-medium focus:z-10 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500',
                                'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-900/50 dark:border-emerald-700 dark:text-emerald-300' => $dateRange === '30',
                                'bg-white border-gray-300 text-gray-700 hover:bg-gray-50 dark:bg-zinc-800 dark:border-zinc-600 dark:text-gray-300 dark:hover:bg-zinc-700' => $dateRange !== '30'
                            ])
                            wire:click="updateDateRange('30')"
                        >
                            30 days
                        </button>
                        <button 
                            type="button" 
                            @class([
                                'relative -ml-px inline-flex items-center px-4 py-2 rounded-r-md border text-sm font-medium focus:z-10 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500',
                                'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-900/50 dark:border-emerald-700 dark:text-emerald-300' => $dateRange === '90',
                                'bg-white border-gray-300 text-gray-700 hover:bg-gray-50 dark:bg-zinc-800 dark:border-zinc-600 dark:text-gray-300 dark:hover:bg-zinc-700' => $dateRange !== '90'
                            ])
                            wire:click="updateDateRange('90')"
                        >
                            90 days
                        </button>
                    </div>

                    <!-- Period Selection -->
                    <div class="inline-flex rounded-md shadow-sm">
                        <button 
                            type="button" 
                            @class([
                                'relative inline-flex items-center px-4 py-2 rounded-l-md border text-sm font-medium focus:z-10 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500',
                                'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-900/50 dark:border-emerald-700 dark:text-emerald-300' => $period === 'daily',
                                'bg-white border-gray-300 text-gray-700 hover:bg-gray-50 dark:bg-zinc-800 dark:border-zinc-600 dark:text-gray-300 dark:hover:bg-zinc-700' => $period !== 'daily'
                            ])
                            wire:click="updatePeriod('daily')"
                        >
                            Daily
                        </button>
                        <button 
                            type="button" 
                            @class([
                                'relative -ml-px inline-flex items-center px-4 py-2 border text-sm font-medium focus:z-10 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500',
                                'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-900/50 dark:border-emerald-700 dark:text-emerald-300' => $period === 'weekly',
                                'bg-white border-gray-300 text-gray-700 hover:bg-gray-50 dark:bg-zinc-800 dark:border-zinc-600 dark:text-gray-300 dark:hover:bg-zinc-700' => $period !== 'weekly'
                            ])
                            wire:click="updatePeriod('weekly')"
                        >
                            Weekly
                        </button>
                        <button 
                            type="button" 
                            @class([
                                'relative -ml-px inline-flex items-center px-4 py-2 rounded-r-md border text-sm font-medium focus:z-10 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500',
                                'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-900/50 dark:border-emerald-700 dark:text-emerald-300' => $period === 'monthly',
                                'bg-white border-gray-300 text-gray-700 hover:bg-gray-50 dark:bg-zinc-800 dark:border-zinc-600 dark:text-gray-300 dark:hover:bg-zinc-700' => $period !== 'monthly'
                            ])
                            wire:click="updatePeriod('monthly')"
                        >
                            Monthly
                        </button>
                    </div>

                    <!-- Export Button -->
                    <button 
                        wire:click="exportReport"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
                    >
                        <span wire:loading.remove wire:target="exportReport">
                            Export Report
                        </span>
                        <span wire:loading wire:target="exportReport">
                            Exporting...
                        </span>
                    </button>
                </div>
            </div>

            <!-- Overview Stats -->
            <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Total Revenue -->
                <div class="bg-white dark:bg-zinc-900 overflow-hidden rounded-lg border border-gray-200 dark:border-zinc-700">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        Total Revenue
                                    </dt>
                                    <dd>
                                        <div class="text-lg font-medium text-gray-900 dark:text-white">
                                            ${{ number_format($metrics['total_revenue'], 2) }}
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Success Rate -->
                <div class="bg-white dark:bg-zinc-900 overflow-hidden rounded-lg border border-gray-200 dark:border-zinc-700">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        Payment Success Rate
                                    </dt>
                                    <dd>
                                        <div class="text-lg font-medium text-gray-900 dark:text-white">
                                            {{ $metrics['payment_success_rate'] }}%
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $metrics['failed_payments'] }} failed payments
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Subscriptions -->
                <div class="bg-white dark:bg-zinc-900 overflow-hidden rounded-lg border border-gray-200 dark:border-zinc-700">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        Total Subscriptions
                                    </dt>
                                    <dd>
                                        <div class="text-lg font-medium text-gray-900 dark:text-white">
                                            {{ number_format($metrics['total_subscriptions']) }}
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Refunds -->
                <div class="bg-white dark:bg-zinc-900 overflow-hidden rounded-lg border border-gray-200 dark:border-zinc-700">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        Total Refunds
                                    </dt>
                                    <dd>
                                        <div class="text-lg font-medium text-gray-900 dark:text-white">
                                            ${{ number_format($metrics['refund_amount'], 2) }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $metrics['refund_count'] }} refunds
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue by Plan -->
            <div class="mt-8">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-4">Revenue by Plan</h4>
                <div class="bg-white dark:bg-zinc-900 shadow overflow-hidden rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                            <thead class="bg-gray-50 dark:bg-zinc-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Plan
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Revenue
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Subscriptions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-zinc-900 divide-y divide-gray-200 dark:divide-zinc-700">
                                @foreach($metrics['revenue_by_plan'] as $plan)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $plan['plan'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            ${{ number_format($plan['revenue'], 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ number_format($plan['count']) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Revenue Trend Chart -->
            <div class="mt-8">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-4">
                    Revenue Trend
                </h4>
                <div class="relative h-80">
                    <canvas x-data="{
                        chart: null,
                        init() {
                            this.chart = new Chart(this.$el, {
                                type: 'line',
                                data: {
                                    labels: {{ json_encode($metrics['revenue_by_period']->pluck('period')->toArray()) }},
                                    datasets: [{
                                        label: 'Revenue',
                                        data: {{ json_encode($metrics['revenue_by_period']->pluck('revenue')->toArray()) }},
                                        borderColor: '#059669',
                                        backgroundColor: '#059669',
                                        tension: 0.4
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            display: false
                                        }
                                    },
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            grid: {
                                                display: true,
                                                color: 'rgba(0, 0, 0, 0.1)',
                                            },
                                            ticks: {
                                                callback: function(value) {
                                                    return '$' + value;
                                                }
                                            }
                                        },
                                        x: {
                                            grid: {
                                                display: false
                                            }
                                        }
                                    }
                                }
                            });
                        }
                    }" wire:ignore></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

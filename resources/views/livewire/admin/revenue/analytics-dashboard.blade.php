<?php

use Livewire\Volt\Component;
use App\Services\RevenueAnalyticsService;
use Livewire\Attributes\Computed;
use function Livewire\Volt\{state, mount};

new class extends Component {
    public $dateRange = '30'; // days
    public $period = 'daily'; // daily, weekly, monthly
    public $loading = false;
    public $exportFormat = 'csv';
    public $planFilter = 'all';
    
    public function mount(RevenueAnalyticsService $revenueService) {
        $this->revenueService = $revenueService;
    }
    
    public function getRevenueMetrics() {
        return $this->revenueService->getRevenueMetrics(
            $this->dateRange,
            $this->period
        );
        
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
            'weekly' => DB::raw('YEARWEEK(subscriptions.created_at) as period'),
            'monthly' => DB::raw('DATE_FORMAT(subscriptions.created_at, "%Y-%m") as period'),
            default => DB::raw('DATE(subscriptions.created_at) as period'), // daily
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
            ->where('subscriptions.status', 'active')
            ->whereBetween('subscriptions.created_at', [$startDate, $endDate])
            ->whereNull('subscriptions.deleted_at')
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
    }

    #[Computed]
    public function metrics() {
        return $this->getRevenueMetrics();
    }

    public function updateDateRange($days) {
        $this->dateRange = $days;
    }

    public function updatePeriod($period) {
        $this->period = $period;
    }

    public function exportReport() {
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
    }
}
?>

<div class="max-w-7xl mx-auto backdrop-blur-sm bg-white/80 dark:bg-neutral-800/80 shadow-xl rounded-3xl p-6 lg:p-8 mt-8 border border-gray-100/40 dark:border-neutral-700/50 transition-all duration-300 relative overflow-hidden space-y-8">
    <!-- Header with glass morphism effect -->
    <div class="backdrop-blur-sm bg-white/80 dark:bg-zinc-800/80 shadow-xl rounded-2xl p-6 border border-gray-100/40 dark:border-zinc-700/50 transition-all duration-300 relative overflow-hidden">
        <!-- Decorative elements -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-emerald-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-gradient-to-tr from-teal-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
        
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="relative">
                    <h2 class="text-2xl md:text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                        <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-500 to-teal-400">
                            Revenue Analytics
                        </span>
                    </h2>
                    <!-- Animated decorative element -->
                    <div class="absolute -bottom-2 left-0 h-1 w-16 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full animate-pulse"></div>
                </div>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Track revenue metrics and payment performance across time periods
                </p>
            </div>
            
            <div class="flex flex-wrap gap-3">
                <!-- Export Button -->
                <button 
                    wire:click="exportReport"
                    wire:loading.attr="disabled"
                    class="relative overflow-hidden inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-medium bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white transition-all duration-300 shadow-sm hover:shadow group"
                >
                    <span class="relative z-10 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span wire:loading.remove wire:target="exportReport">
                            Export Report
                        </span>
                        <span wire:loading wire:target="exportReport">
                            Exporting...
                        </span>
                    </span>
                    <!-- Shimmer effect -->
                    <span class="absolute top-0 right-full w-12 h-full bg-white/30 transform rotate-12 translate-x-0 transition-transform duration-1000 ease-out group-hover:translate-x-[400%]"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Filters Section with glass morphism -->
    <div class="backdrop-blur-sm bg-white/80 dark:bg-zinc-800/80 shadow-lg rounded-2xl p-5 border border-gray-100/40 dark:border-zinc-700/50 transition-all duration-300">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <!-- Date Range Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date Range</label>
                <div class="inline-flex rounded-xl shadow-sm bg-gray-50/50 dark:bg-zinc-900/50 p-1 border border-gray-100 dark:border-zinc-700/50">
                    <button 
                        type="button" 
                        @class([
                            'relative inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium transition-all duration-200',
                            'bg-white dark:bg-zinc-800 shadow text-emerald-600 dark:text-emerald-400' => $dateRange === '7',
                            'text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white' => $dateRange !== '7'
                        ])
                        wire:click="updateDateRange('7')"
                    >
                        7 days
                    </button>
                    <button 
                        type="button" 
                        @class([
                            'relative inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium transition-all duration-200',
                            'bg-white dark:bg-zinc-800 shadow text-emerald-600 dark:text-emerald-400' => $dateRange === '30',
                            'text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white' => $dateRange !== '30'
                        ])
                        wire:click="updateDateRange('30')"
                    >
                        30 days
                    </button>
                    <button 
                        type="button" 
                        @class([
                            'relative inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium transition-all duration-200',
                            'bg-white dark:bg-zinc-800 shadow text-emerald-600 dark:text-emerald-400' => $dateRange === '90',
                            'text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white' => $dateRange !== '90'
                        ])
                        wire:click="updateDateRange('90')"
                    >
                        90 days
                    </button>
                </div>
            </div>

            <!-- Period Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Group By</label>
                <div class="inline-flex rounded-xl shadow-sm bg-gray-50/50 dark:bg-zinc-900/50 p-1 border border-gray-100 dark:border-zinc-700/50">
                    <button 
                        type="button" 
                        @class([
                            'relative inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium transition-all duration-200',
                            'bg-white dark:bg-zinc-800 shadow text-emerald-600 dark:text-emerald-400' => $period === 'daily',
                            'text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white' => $period !== 'daily'
                        ])
                        wire:click="updatePeriod('daily')"
                    >
                        Daily
                    </button>
                    <button 
                        type="button" 
                        @class([
                            'relative inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium transition-all duration-200',
                            'bg-white dark:bg-zinc-800 shadow text-emerald-600 dark:text-emerald-400' => $period === 'weekly',
                            'text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white' => $period !== 'weekly'
                        ])
                        wire:click="updatePeriod('weekly')"
                    >
                        Weekly
                    </button>
                    <button 
                        type="button" 
                        @class([
                            'relative inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium transition-all duration-200',
                            'bg-white dark:bg-zinc-800 shadow text-emerald-600 dark:text-emerald-400' => $period === 'monthly',
                            'text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white' => $period !== 'monthly'
                        ])
                        wire:click="updatePeriod('monthly')"
                    >
                        Monthly
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Overview Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Total Revenue -->
        <div class="group bg-white/60 dark:bg-zinc-800/40 rounded-xl overflow-hidden border border-gray-200/60 dark:border-zinc-700/40 backdrop-blur-sm shadow-sm transition-all duration-300 hover:shadow-md">
            <div class="px-5 py-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-emerald-50 dark:bg-emerald-900/20 rounded-full p-3 border border-emerald-100 dark:border-emerald-800/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-500" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    
                    <div class="ml-4 flex-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                            Total Revenue
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                            ${{ number_format($this->metrics['total_revenue'], 2) }}
                        </dd>
                    </div>
                </div>
            </div>
            <div class="w-full bg-gradient-to-r from-emerald-500 to-teal-500 h-1 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
        </div>
        
        <!-- Payment Success Rate -->
        <div class="group bg-white/60 dark:bg-zinc-800/40 rounded-xl overflow-hidden border border-gray-200/60 dark:border-zinc-700/40 backdrop-blur-sm shadow-sm transition-all duration-300 hover:shadow-md">
            <div class="px-5 py-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-50 dark:bg-green-900/20 rounded-full p-3 border border-green-100 dark:border-green-800/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    
                    <div class="ml-4 flex-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                            Payment Success Rate
                        </dt>
                        <dd class="mt-1">
                            <div class="text-3xl font-semibold text-gray-900 dark:text-white">
                                {{ $this->metrics['payment_success_rate'] }}%
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                {{ $this->metrics['failed_payments'] }} failed payments
                            </div>
                        </dd>
                    </div>
                </div>
            </div>
            <div class="w-full bg-gradient-to-r from-green-500 to-emerald-500 h-1 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
        </div>
        
        <!-- Total Subscriptions -->
        <div class="group bg-white/60 dark:bg-zinc-800/40 rounded-xl overflow-hidden border border-gray-200/60 dark:border-zinc-700/40 backdrop-blur-sm shadow-sm transition-all duration-300 hover:shadow-md">
            <div class="px-5 py-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-50 dark:bg-blue-900/20 rounded-full p-3 border border-blue-100 dark:border-blue-800/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                    
                    <div class="ml-4 flex-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                            Total Subscriptions
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                            {{ number_format($this->metrics['total_subscriptions']) }}
                        </dd>
                    </div>
                </div>
            </div>
            <div class="w-full bg-gradient-to-r from-blue-500 to-sky-500 h-1 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
        </div>
        
        <!-- Refunds -->
        <div class="group bg-white/60 dark:bg-zinc-800/40 rounded-xl overflow-hidden border border-gray-200/60 dark:border-zinc-700/40 backdrop-blur-sm shadow-sm transition-all duration-300 hover:shadow-md">
            <div class="px-5 py-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-50 dark:bg-red-900/20 rounded-full p-3 border border-red-100 dark:border-red-800/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                    </div>
                    
                    <div class="ml-4 flex-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                            Total Refunds
                        </dt>
                        <dd class="mt-1">
                            <div class="text-3xl font-semibold text-gray-900 dark:text-white">
                                ${{ number_format($this->metrics['refund_amount'], 2) }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                {{ $this->metrics['refund_count'] }} refunds
                            </div>
                        </dd>
                    </div>
                </div>
            </div>
            <div class="w-full bg-gradient-to-r from-red-500 to-pink-500 h-1 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
        </div>
    </div>

    <!-- Revenue Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Revenue Trend Chart -->
        <div class="lg:col-span-2 backdrop-blur-sm bg-white/80 dark:bg-zinc-800/80 shadow-lg rounded-2xl p-6 border border-gray-100/40 dark:border-zinc-700/50 transition-all duration-300 relative overflow-hidden">
            <!-- Decorative elements -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-blue-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
            <div class="absolute bottom-0 left-0 w-80 h-80 bg-gradient-to-tr from-teal-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
            
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-medium text-gray-900 dark:text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
                    </svg>
                    Revenue Trend
                </h3>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ ucfirst($period) }} view · Last {{ $dateRange }} days
                </span>
            </div>
            
            <div class="relative h-[350px] w-full">
                <canvas x-data="{
                    chart: null,
                    init() {
                        this.chart = new Chart(this.$el, {
                            type: 'line',
                            data: {
                                labels: {{ json_encode($this->metrics['revenue_by_period']->pluck('period')->toArray()) }},
                                datasets: [{
                                    label: 'Revenue',
                                    data: {{ json_encode($this->metrics['revenue_by_period']->pluck('revenue')->toArray()) }},
                                    borderColor: '#10b981',
                                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                    fill: true,
                                    tension: 0.4,
                                    borderWidth: 3,
                                    pointRadius: 4,
                                    pointBackgroundColor: '#10b981',
                                    pointBorderColor: '#fff',
                                    pointHoverRadius: 6,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: {
                                    mode: 'index',
                                    intersect: false,
                                },
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                                        titleColor: '#1f2937',
                                        bodyColor: '#4b5563',
                                        borderColor: '#e5e7eb',
                                        borderWidth: 1,
                                        padding: 10,
                                        boxPadding: 5,
                                        usePointStyle: true,
                                        callbacks: {
                                            label: function(context) {
                                                return `Revenue: $${context.raw.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        grid: {
                                            display: true,
                                            color: 'rgba(0, 0, 0, 0.05)',
                                        },
                                        ticks: {
                                            callback: function(value) {
                                                return '$' + value.toLocaleString('en-US');
                                            },
                                            padding: 10
                                        }
                                    },
                                    x: {
                                        grid: {
                                            display: false
                                        },
                                        ticks: {
                                            maxRotation: 0,
                                            padding: 10
                                        }
                                    }
                                }
                            }
                        });
                    }
                }" wire:ignore></canvas>
            </div>
        </div>

        <!-- Revenue by Plan -->
        <div class="backdrop-blur-sm bg-white/80 dark:bg-zinc-800/80 shadow-lg rounded-2xl overflow-hidden border border-gray-100/40 dark:border-zinc-700/50 transition-all duration-300 relative">
            <!-- Decorative elements -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-emerald-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
            <div class="absolute bottom-0 left-0 w-80 h-80 bg-gradient-to-tr from-teal-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
            
            <div class="p-6 border-b border-gray-100 dark:border-zinc-700/50">
                <h3 class="font-medium text-gray-900 dark:text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z" clip-rule="evenodd" />
                    </svg>
                    Revenue by Plan
                </h3>
            </div>
            
            <div class="overflow-x-auto scrollbar-thin scrollbar-thumb-gray-200 dark:scrollbar-thumb-zinc-700 scrollbar-track-transparent">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                    <thead class="bg-gray-50/50 dark:bg-zinc-800/50">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Plan
                            </th>
                            <th class="px-6 py-3.5 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Revenue
                            </th>
                            <th class="px-6 py-3.5 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Users
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white/60 dark:bg-zinc-800/60 backdrop-blur-sm divide-y divide-gray-200 dark:divide-zinc-700">
                        @forelse($this->metrics['revenue_by_plan'] as $plan)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-zinc-700/30 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="h-8 w-8 rounded-lg bg-gradient-to-br from-emerald-500/20 to-teal-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm font-medium">
                                            {{ substr($plan['plan'], 0, 1) }}
                                        </span>
                                        <span class="ml-3 font-medium text-gray-900 dark:text-white">
                                            {{ $plan['plan'] }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                    <span class="font-semibold text-gray-900 dark:text-white">${{ number_format($plan['revenue'], 2) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                    <div class="flex items-center justify-end">
                                        <span class="font-medium text-gray-900 dark:text-white">{{ number_format($plan['count']) }}</span>
                                        <!-- Circular progress indicator showing percentage of total -->
                                        @php
                                            $percentage = $this->metrics['total_subscriptions'] > 0 
                                                ? ($plan['count'] / $this->metrics['total_subscriptions']) * 100 
                                                : 0;
                                        @endphp
                                        <div class="ml-2 h-4 w-16 bg-gray-100 dark:bg-zinc-700 rounded-full overflow-hidden">
                                            <div 
                                                class="h-full bg-gradient-to-r from-emerald-500 to-teal-500" 
                                                style="width: {{ $percentage }}%"
                                            ></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-8 whitespace-nowrap text-center text-sm text-gray-500 dark:text-gray-400">
                                    No plan data available
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Total Summary Row -->
            <div class="p-4 bg-gray-50/80 dark:bg-zinc-800/50 border-t border-gray-200/60 dark:border-zinc-700/50">
                <div class="flex justify-between items-center">
                    <span class="font-medium text-gray-700 dark:text-gray-300">Total</span>
                    <div class="flex items-center gap-8">
                        <span class="font-bold text-gray-900 dark:text-white">${{ number_format($this->metrics['total_revenue'], 2) }}</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ number_format($this->metrics['total_subscriptions']) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

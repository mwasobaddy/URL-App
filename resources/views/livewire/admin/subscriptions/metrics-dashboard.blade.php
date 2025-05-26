<?php

use App\Models\Subscription;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Volt\Component;

new class extends Component
{
    public $dateRange = '30'; // days
    public $loading = false;

    public function getSubscriptionMetrics()
    {
        $endDate = now();
        $startDate = now()->subDays($this->dateRange);
        
        // Active subscriptions trend
        $activeSubscriptions = Subscription::where('status', 'active')
            ->whereNull('cancelled_at')
            ->count();

        // Trial conversions
        $trialConversions = Subscription::where('status', 'active')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', now())
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();

        $totalTrials = Subscription::whereNotNull('trial_ends_at')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $conversionRate = $totalTrials > 0 ? ($trialConversions / $totalTrials) * 100 : 0;

        // Churn analysis
        $cancelledSubscriptions = Subscription::whereNotNull('cancelled_at')
            ->whereBetween('cancelled_at', [$startDate, $endDate])
            ->count();

        $totalActiveStart = Subscription::where('status', 'active')
            ->where('created_at', '<', $startDate)
            ->count();

        $churnRate = $totalActiveStart > 0 ? ($cancelledSubscriptions / $totalActiveStart) * 100 : 0;

        // MRR/ARR Calculations
        $mrr = Subscription::where('status', 'active')
            ->whereNull('cancelled_at')
            ->whereNull('trial_ends_at')
            ->orWhere('trial_ends_at', '<', now())
            ->with('planVersion')
            ->get()
            ->sum(function ($subscription) {
                return $subscription->interval === 'yearly'
                    ? $subscription->planVersion->yearly_price / 12
                    : $subscription->planVersion->monthly_price;
            });

        $arr = $mrr * 12;

        // Daily Revenue Trend
        $dailyRevenue = Subscription::where('status', 'active')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(CASE 
                WHEN interval = "yearly" THEN plan_versions.yearly_price
                ELSE plan_versions.monthly_price 
                END) as revenue')
            ->join('plan_versions', 'subscriptions.plan_version_id', '=', 'plan_versions.id')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'active_count' => $activeSubscriptions,
            'trial_conversions' => $trialConversions,
            'conversion_rate' => round($conversionRate, 2),
            'churn_rate' => round($churnRate, 2),
            'mrr' => round($mrr, 2),
            'arr' => round($arr, 2),
            'revenue_trend' => $dailyRevenue,
        ];
    }

    public function with(): array
    {
        return [
            'metrics' => $this->getSubscriptionMetrics()
        ];
    }

    public function updateDateRange($days)
    {
        $this->dateRange = $days;
    }
}

?>

<div>
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:p-6">
            <div class="sm:flex sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Subscription Metrics
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Track key subscription performance indicators
                    </p>
                </div>
                <div class="mt-4 sm:mt-0">
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
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Active Subscriptions -->
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
                                        Active Subscriptions
                                    </dt>
                                    <dd>
                                        <div class="text-lg font-medium text-gray-900 dark:text-white">
                                            {{ number_format($metrics['active_count']) }}
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Trial Conversions -->
                <div class="bg-white dark:bg-zinc-900 overflow-hidden rounded-lg border border-gray-200 dark:border-zinc-700">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        Trial Conversion Rate
                                    </dt>
                                    <dd>
                                        <div class="text-lg font-medium text-gray-900 dark:text-white">
                                            {{ $metrics['conversion_rate'] }}%
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $metrics['trial_conversions'] }} conversions
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Churn Rate -->
                <div class="bg-white dark:bg-zinc-900 overflow-hidden rounded-lg border border-gray-200 dark:border-zinc-700">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        Churn Rate
                                    </dt>
                                    <dd>
                                        <div class="text-lg font-medium text-gray-900 dark:text-white">
                                            {{ $metrics['churn_rate'] }}%
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MRR/ARR -->
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
                                        Monthly Recurring Revenue
                                    </dt>
                                    <dd>
                                        <div class="text-lg font-medium text-gray-900 dark:text-white">
                                            ${{ number_format($metrics['mrr'], 2) }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            ARR: ${{ number_format($metrics['arr'], 2) }}
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
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
                                    labels: {{ json_encode($metrics['revenue_trend']->pluck('date')->toArray()) }},
                                    datasets: [{
                                        label: 'Daily Revenue',
                                        data: {{ json_encode($metrics['revenue_trend']->pluck('revenue')->toArray()) }},
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

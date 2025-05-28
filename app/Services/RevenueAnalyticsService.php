<?php

namespace App\Services;

use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RevenueAnalyticsService
{
    public function getRevenueMetrics(string $dateRange, string $period = 'daily'): array
    {
        $endDate = now();
        $startDate = now()->subDays((int) $dateRange);
        
        // Base query for all subscription payments
        $query = Subscription::query()
            ->with(['plan', 'planVersion'])
            ->where('status', 'active')
            ->whereBetween('subscriptions.created_at', [$startDate, $endDate]);

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

        // Revenue by period using proper table prefixes
        $periodQuery = match($period) {
            'weekly' => DB::raw('YEARWEEK(subscriptions.created_at) as period'),
            'monthly' => DB::raw('DATE_FORMAT(subscriptions.created_at, "%Y-%m") as period'),
            default => DB::raw('DATE(subscriptions.created_at) as period'), // daily
        };

        $revenueByPeriod = DB::table('subscriptions')
            ->select([
                $periodQuery,
                DB::raw('COUNT(*) as subscriptions'),
                DB::raw('SUM(CASE 
                    WHEN interval = "yearly" THEN plan_versions.yearly_price
                    ELSE plan_versions.monthly_price 
                    END) as revenue')
            ])
            ->join('plan_versions', 'subscriptions.plan_version_id', '=', 'plan_versions.id')
            ->where('subscriptions.status', 'active')
            ->whereBetween('subscriptions.created_at', [$startDate, $endDate])
            ->whereNull('subscriptions.deleted_at')
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Payment success/failure rates
        $totalPayments = $query->count();
        $failedPayments = $query->where('subscriptions.status', 'payment_failed')->count();
        $successRate = $totalPayments > 0 ? (($totalPayments - $failedPayments) / $totalPayments) * 100 : 0;

        // Refund tracking
        $refunds = $query->where('subscriptions.status', 'refunded')->get();
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
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanVersion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'plan_id',
        'version',
        'name',
        'description',
        'monthly_price',
        'yearly_price',
        'features',
        'is_active',
        'valid_from',
        'valid_until',
        'paypal_monthly_plan_id',
        'paypal_yearly_plan_id',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function calculateProration(PlanVersion $newVersion, string $interval): array
    {
        $currentPrice = $interval === 'monthly' ? $this->monthly_price : $this->yearly_price;
        $newPrice = $interval === 'monthly' ? $newVersion->monthly_price : $newVersion->yearly_price;

        // Calculate remaining days in current billing cycle
        $daysInCycle = $interval === 'monthly' ? 30 : 365;
        $now = now();
        $cycleEnd = $now->copy()->addDays($daysInCycle);
        $remainingDays = $now->diffInDays($cycleEnd);

        // Calculate prorated refund for current plan
        $dailyRate = $currentPrice / $daysInCycle;
        $refundAmount = round($dailyRate * $remainingDays, 2);

        // Calculate prorated charge for new plan
        $newDailyRate = $newPrice / $daysInCycle;
        $chargeAmount = round($newDailyRate * $remainingDays, 2);

        // Calculate final amount (can be positive or negative)
        $netAmount = $chargeAmount - $refundAmount;

        return [
            'current_plan_refund' => $refundAmount,
            'new_plan_charge' => $chargeAmount,
            'net_amount' => $netAmount,
            'remaining_days' => $remainingDays,
            'effective_date' => $now->toDateTimeString(),
            'next_billing_date' => $cycleEnd->toDateTimeString(),
        ];
    }
}

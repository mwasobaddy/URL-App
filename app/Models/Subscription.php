<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'plan_id',
        'plan_version_id',
        'status',
        'paypal_subscription_id',
        'paypal_plan_id',
        'interval',
        'trial_ends_at',
        'current_period_starts_at',
        'current_period_ends_at',
        'cancelled_at',
        'ends_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'current_period_starts_at' => 'datetime',
        'current_period_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function planVersion()
    {
        return $this->belongsTo(PlanVersion::class, 'plan_version_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && 
               ($this->ends_at === null || $this->ends_at->isFuture());
    }

    public function isCancelled(): bool
    {
        return $this->cancelled_at !== null;
    }

    public function isOnTrial(): bool
    {
        return $this->trial_ends_at !== null && 
               $this->trial_ends_at->isFuture();
    }

    public function hasEndedTrial(): bool
    {
        return $this->trial_ends_at !== null && 
               $this->trial_ends_at->isPast();
    }

    public function cancel(): void
    {
        $this->cancelled_at = now();
        $this->status = 'cancelled';
        $this->save();
    }

    public function resume(): void
    {
        $this->cancelled_at = null;
        $this->ends_at = null;
        $this->status = 'active';
        $this->save();
    }

    public function switchVersion(PlanVersion $newVersion): bool
    {
        // Use the subscription service to handle version switching
        return app(SubscriptionService::class)->switchPlanVersion($this, $newVersion);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'paypal_plan_id',
        'monthly_price',
        'yearly_price',
        'features',
        'max_lists',
        'max_urls_per_list',
        'max_team_members',
        'is_featured',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'monthly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function versions()
    {
        return $this->hasMany(PlanVersion::class);
    }

    public function getPrice(string $interval = 'monthly'): float
    {
        return $interval === 'yearly' ? $this->yearly_price : $this->monthly_price;
    }

    public function getYearlySavingsPercentage(): int
    {
        $monthlyTotal = $this->monthly_price * 12;
        $yearlyTotal = $this->yearly_price;
        
        if ($monthlyTotal <= 0) {
            return 0;
        }

        return (int) (100 - ($yearlyTotal / $monthlyTotal * 100));
    }

    public function getCurrentVersion(): ?PlanVersion
    {
        return $this->versions()
            ->where('is_active', true)
            ->where(function ($query) {
                $now = now();
                $query->where(function ($q) use ($now) {
                    $q->whereNull('valid_from')
                      ->orWhere('valid_from', '<=', $now);
                })->where(function ($q) use ($now) {
                    $q->whereNull('valid_until')
                      ->orWhere('valid_until', '>', $now);
                });
            })
            ->latest('valid_from')
            ->first();
    }

    public function getVersion(string $version): ?PlanVersion
    {
        return $this->versions()->where('version', $version)->first();
    }

    public function createVersion(array $attributes): PlanVersion
    {
        // Generate version number if not provided
        if (!isset($attributes['version'])) {
            $latestVersion = $this->versions()->max('version') ?? '0.0.0';
            $versionParts = explode('.', $latestVersion);
            $versionParts[2] = (int)$versionParts[2] + 1;
            $attributes['version'] = implode('.', $versionParts);
        }

        // Ensure only one active version at a time if this is being set as active
        if (isset($attributes['is_active']) && $attributes['is_active']) {
            $this->versions()->where('is_active', true)->update(['is_active' => false]);
        }

        // Create new version
        return $this->versions()->create($attributes);
    }
}

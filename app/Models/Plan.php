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
}

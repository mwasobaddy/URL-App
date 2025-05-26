<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'is_active',
        'exchange_rate',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'exchange_rate' => 'decimal:4',
    ];

    public function getFormattedAmount(float $amount): string
    {
        return $this->symbol . number_format($amount * $this->exchange_rate, 2);
    }

    public function convertToBase(float $amount): float
    {
        return $amount / $this->exchange_rate;
    }

    public function convertFromBase(float $amount): float
    {
        return $amount * $this->exchange_rate;
    }
}

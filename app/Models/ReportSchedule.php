<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'frequency',
        'configuration',
    ];

    protected $casts = [
        'configuration' => 'array',
    ];
}

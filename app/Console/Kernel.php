<?php

namespace App\Console;

use App\Services\SubscriptionService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Check for subscription renewals daily at midnight
        $schedule->call(function () {
            app(SubscriptionService::class)->checkAndSendRenewalNotifications();
        })->dailyAt('00:00');

        // Generate scheduled reports
        $schedule->command('reports:generate')->dailyAt('01:00');
    }
}
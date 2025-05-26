<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReportSchedule;
use App\Jobs\GenerateRevenueReport;
use Carbon\Carbon;

class GenerateScheduledReports extends Command
{
    protected $signature = 'reports:generate';
    protected $description = 'Generate scheduled reports based on configured schedules';

    public function handle(): void
    {
        $schedules = ReportSchedule::all();

        foreach ($schedules as $schedule) {
            if ($this->shouldGenerateReport($schedule)) {
                GenerateRevenueReport::dispatch($schedule);
            }
        }
    }

    protected function shouldGenerateReport(ReportSchedule $schedule): bool
    {
        $now = now();

        return match ($schedule->frequency) {
            'daily' => true,
            'weekly' => $now->dayOfWeek === ($schedule->configuration['day_of_week'] ?? 1),
            'monthly' => $now->day === ($schedule->configuration['day_of_month'] ?? 1),
            default => false,
        };
    }
}

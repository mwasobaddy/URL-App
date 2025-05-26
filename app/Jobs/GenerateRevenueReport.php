<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Exports\RevenueReport;
use App\Models\ReportSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class GenerateRevenueReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $schedule;
    protected $startDate;
    protected $endDate;

    public function __construct(ReportSchedule $schedule, ?Carbon $startDate = null, ?Carbon $endDate = null)
    {
        $this->schedule = $schedule;
        $this->startDate = $startDate ?? $this->getDefaultStartDate();
        $this->endDate = $endDate ?? $this->getDefaultEndDate();
    }

    public function handle(): void
    {
        $report = new RevenueReport(
            $this->startDate,
            $this->endDate,
            $this->schedule->configuration['metrics'] ?? ['revenue' => true]
        );

        // Generate report in all formats for scheduled reports
        $formats = ['xlsx', 'csv', 'pdf'];
        $files = [];

        foreach ($formats as $format) {
            $filename = sprintf(
                'revenue-report-%s-to-%s.%s',
                $this->startDate->format('Y-m-d'),
                $this->endDate->format('Y-m-d'),
                $format
            );

            Excel::store($report, "reports/{$filename}", 's3');
            $files[] = [
                'name' => $filename,
                'url' => Storage::disk('s3')->temporaryUrl("reports/{$filename}", now()->addHours(24))
            ];
        }

        // Send email to recipients if configured
        if (!empty($this->schedule->configuration['recipients'])) {
            $this->sendReportEmail($files);
        }
    }

    protected function getDefaultStartDate(): Carbon
    {
        return match ($this->schedule->frequency) {
            'daily' => now()->subDay(),
            'weekly' => now()->subWeek(),
            'monthly' => now()->subMonth(),
            default => now()->startOfMonth(),
        };
    }

    protected function getDefaultEndDate(): Carbon
    {
        return match ($this->schedule->frequency) {
            'daily' => now(),
            'weekly' => now(),
            'monthly' => now(),
            default => now()->endOfMonth(),
        };
    }

    protected function sendReportEmail(array $files): void
    {
        Mail::send(
            'emails.revenue-report',
            [
                'startDate' => $this->startDate->format('Y-m-d'),
                'endDate' => $this->endDate->format('Y-m-d'),
                'files' => $files,
            ],
            function ($message) use ($files) {
                $message
                    ->to($this->schedule->configuration['recipients'])
                    ->subject('Revenue Report ' . $this->startDate->format('Y-m-d') . ' to ' . $this->endDate->format('Y-m-d'));

                foreach ($files as $file) {
                    $message->attach(Storage::disk('s3')->path("reports/{$file['name']}"));
                }
            }
        );
    }
}

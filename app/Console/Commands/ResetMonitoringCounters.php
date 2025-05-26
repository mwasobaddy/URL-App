<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SystemMonitoringService;

class ResetMonitoringCounters extends Command
{
    protected $signature = 'monitoring:reset-counters';
    protected $description = 'Reset daily monitoring counters';

    protected $monitoringService;

    public function __construct(SystemMonitoringService $monitoringService)
    {
        parent::__construct();
        $this->monitoringService = $monitoringService;
    }

    public function handle(): void
    {
        $this->monitoringService->resetDailyCounters();
        $this->info('Successfully reset monitoring counters.');
    }
}

<?php

use function Livewire\Volt\{state, computed, mount};
use Carbon\Carbon;
use App\Models\Revenue;
use App\Models\ReportSchedule;
use App\Jobs\GenerateRevenueReport;
use App\Exports\RevenueReport;

$state = [
    'dateRange' => [
        'start' => null,
        'end' => null,
    ],
    'format' => 'xlsx', // xlsx, csv, or pdf
    'includeMetrics' => [
        'revenue' => true,
        'subscriptions' => true,
        'taxes' => true,
        'refunds' => true,
    ],
    'schedule' => [
        'frequency' => 'never', // never, daily, weekly, monthly
        'dayOfWeek' => 1, // 1 = Monday
        'dayOfMonth' => 1,
        'recipients' => [],
    ],
];

$mount = function () {
    // Set default date range to current month
    $this->dateRange['start'] = Carbon::now()->startOfMonth()->format('Y-m-d');
    $this->dateRange['end'] = Carbon::now()->endOfMonth()->format('Y-m-d');
};

$updateSchedule = function () {
    if ($this->schedule['frequency'] === 'never') {
        return;
    }

    ReportSchedule::updateOrCreate(
        ['type' => 'revenue'],
        [
            'frequency' => $this->schedule['frequency'],
            'configuration' => [
                'day_of_week' => $this->schedule['dayOfWeek'],
                'day_of_month' => $this->schedule['dayOfMonth'],
                'metrics' => $this->includeMetrics,
                'recipients' => $this->schedule['recipients'],
            ]
        ]
    );
};

$generateReport = function () {
    $report = new RevenueReport(
        Carbon::parse($this->dateRange['start']),
        Carbon::parse($this->dateRange['end']),
        $this->includeMetrics
    );

    return response()->download(
        $report->download("revenue-report.{$this->format}")
    );
};

?>

<div class="p-6 bg-white rounded-lg shadow-sm">
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">
        Revenue Reports Export
    </h2>

    <!-- Date Range Selection -->
    <div class="grid grid-cols-2 gap-4 mb-6">
        <x-datepicker
            label="Start Date"
            wire:model="dateRange.start"
            :config="['maxDate' => $dateRange['end']]"
        />
        <x-datepicker
            label="End Date"
            wire:model="dateRange.end"
            :config="['minDate' => $dateRange['start']]"
        />
    </div>

    <!-- Export Format -->
    <div class="mb-6">
        <x-native-select
            label="Export Format"
            wire:model="format"
        >
            <option value="xlsx">Excel (XLSX)</option>
            <option value="csv">CSV</option>
            <option value="pdf">PDF</option>
        </x-native-select>
    </div>

    <!-- Metrics Selection -->
    <div class="mb-6">
        <x-card title="Include Metrics">
            <div class="space-y-4">
                <x-toggle
                    label="Revenue Data"
                    wire:model="includeMetrics.revenue"
                />
                <x-toggle
                    label="Subscription Data"
                    wire:model="includeMetrics.subscriptions"
                />
                <x-toggle
                    label="Tax Information"
                    wire:model="includeMetrics.taxes"
                />
                <x-toggle
                    label="Refund Data"
                    wire:model="includeMetrics.refunds"
                />
            </div>
        </x-card>
    </div>

    <!-- Report Scheduling -->
    <div class="mb-6">
        <x-card title="Report Scheduling">
            <div class="space-y-4">
                <x-native-select
                    label="Schedule Frequency"
                    wire:model="schedule.frequency"
                >
                    <option value="never">No Schedule</option>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                </x-native-select>

                @if($schedule['frequency'] === 'weekly')
                    <x-native-select
                        label="Day of Week"
                        wire:model="schedule.dayOfWeek"
                    >
                        <option value="1">Monday</option>
                        <option value="2">Tuesday</option>
                        <option value="3">Wednesday</option>
                        <option value="4">Thursday</option>
                        <option value="5">Friday</option>
                        <option value="6">Saturday</option>
                        <option value="7">Sunday</option>
                    </x-native-select>
                @endif

                @if($schedule['frequency'] === 'monthly')
                    <x-native-select
                        label="Day of Month"
                        wire:model="schedule.dayOfMonth"
                    >
                        @for($i = 1; $i <= 31; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </x-native-select>
                @endif

                @if($schedule['frequency'] !== 'never')
                    <x-input.tags
                        label="Recipients (Email)"
                        wire:model="schedule.recipients"
                        placeholder="Add email and press Enter"
                    />
                @endif
            </div>

            @if($schedule['frequency'] !== 'never')
                <x-slot name="footer">
                    <div class="flex justify-end">
                        <x-button
                            primary
                            wire:click="updateSchedule"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="updateSchedule">
                                Save Schedule
                            </span>
                            <span wire:loading wire:target="updateSchedule">
                                Saving...
                            </span>
                        </x-button>
                    </div>
                </x-slot>
            @endif
        </x-card>
    </div>

    <!-- Export Button -->
    <div class="flex justify-end">
        <x-button
            primary
            wire:click="generateReport"
            wire:loading.attr="disabled"
        >
            <span wire:loading.remove wire:target="generateReport">
                Generate Report
            </span>
            <span wire:loading wire:target="generateReport">
                Generating...
            </span>
        </x-button>
    </div>
</div>

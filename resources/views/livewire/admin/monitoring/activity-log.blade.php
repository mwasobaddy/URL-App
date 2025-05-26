<?php

use function Livewire\Volt\{state, computed, mount};
use App\Services\AuditLogService;
use Carbon\Carbon;

state([
    'filter' => [
        'event' => '',
        'user_id' => '',
        'type' => '',
        'tag' => '',
        'date_from' => '',
        'date_to' => '',
        'per_page' => 15,
    ],
    'selectedTab' => 'all', // all, user, system, security
    'logs' => [],
    'statistics' => [],
]);

$mount = function (AuditLogService $auditLogService) {
    $this->refreshLogs($auditLogService);
    $this->statistics = $auditLogService->getEventStatistics();
};

$refreshLogs = function (AuditLogService $auditLogService) {
    $filters = $this->filter;
    
    if ($this->selectedTab !== 'all') {
        $filters['tag'] = $this->selectedTab;
    }
    
    $this->logs = match ($this->selectedTab) {
        'security' => $auditLogService->getSecurityEvents($filters),
        'system' => $auditLogService->getSystemEvents($filters),
        default => $auditLogService->getActivityLogs($filters),
    };
};

$selectTab = function (string $tab) {
    $this->selectedTab = $tab;
    $this->refreshLogs(app(AuditLogService::class));
};

$resetFilters = function () {
    $this->filter = [
        'event' => '',
        'user_id' => '',
        'type' => '',
        'tag' => '',
        'date_from' => '',
        'date_to' => '',
        'per_page' => 15,
    ];
    $this->refreshLogs(app(AuditLogService::class));
};

$applyFilters = function () {
    $this->refreshLogs(app(AuditLogService::class));
};

?>

<div>
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:p-6">
            <div class="sm:flex sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Activity & Audit Logs
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Monitor user actions, system events, and security alerts
                    </p>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <div @class([
                    'bg-white dark:bg-zinc-900 overflow-hidden rounded-lg border',
                    'border-gray-200 dark:border-zinc-700' => $selectedTab !== 'all',
                    'border-emerald-500 dark:border-emerald-400' => $selectedTab === 'all',
                ])>
                    <button 
                        type="button"
                        wire:click="selectTab('all')"
                        class="w-full p-5"
                    >
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        Total Events
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                        {{ number_format($statistics['total_events']) }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </button>
                </div>

                <div @class([
                    'bg-white dark:bg-zinc-900 overflow-hidden rounded-lg border',
                    'border-gray-200 dark:border-zinc-700' => $selectedTab !== 'user',
                    'border-emerald-500 dark:border-emerald-400' => $selectedTab === 'user',
                ])>
                    <button 
                        type="button"
                        wire:click="selectTab('user')"
                        class="w-full p-5"
                    >
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        User Actions
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                        {{ number_format($statistics['user_actions']) }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </button>
                </div>

                <div @class([
                    'bg-white dark:bg-zinc-900 overflow-hidden rounded-lg border',
                    'border-gray-200 dark:border-zinc-700' => $selectedTab !== 'system',
                    'border-emerald-500 dark:border-emerald-400' => $selectedTab === 'system',
                ])>
                    <button 
                        type="button"
                        wire:click="selectTab('system')"
                        class="w-full p-5"
                    >
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 01-3-3m3 3a3 3 0 100 6h13.5a3 3 0 100-6m-16.5-3a3 3 0 013-3h13.5a3 3 0 013 3m-19.5 0a4.5 4.5 0 01.9-2.7L5.737 5.1a3.375 3.375 0 012.7-1.35h7.126c1.062 0 2.062.5 2.7 1.35l2.587 3.45a4.5 4.5 0 01.9 2.7m0 0a3 3 0 01-3 3m0 3h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008zm-3 6h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        System Events
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                        {{ number_format($statistics['system_events']) }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </button>
                </div>

                <div @class([
                    'bg-white dark:bg-zinc-900 overflow-hidden rounded-lg border',
                    'border-gray-200 dark:border-zinc-700' => $selectedTab !== 'security',
                    'border-emerald-500 dark:border-emerald-400' => $selectedTab === 'security',
                ])>
                    <button 
                        type="button"
                        wire:click="selectTab('security')"
                        class="w-full p-5"
                    >
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                        Security Events
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                        {{ number_format($statistics['security_events']) }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="mt-6 bg-gray-50 dark:bg-zinc-900 rounded-lg p-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <x-input
                        type="search"
                        placeholder="Search events..."
                        wire:model.live.debounce.300ms="filter.event"
                    />

                    <x-native-select
                        wire:model.live="filter.per_page"
                    >
                        <option value="15">15 per page</option>
                        <option value="30">30 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                    </x-native-select>

                    <x-input
                        type="date"
                        placeholder="From date"
                        wire:model.live="filter.date_from"
                    />

                    <x-input
                        type="date"
                        placeholder="To date"
                        wire:model.live="filter.date_to"
                    />
                </div>

                <div class="mt-4 flex justify-end space-x-3">
                    <x-button 
                        wire:click="resetFilters"
                        type="button"
                    >
                        Reset Filters
                    </x-button>
                </div>
            </div>

            <!-- Logs Table -->
            <div class="mt-6 bg-white dark:bg-zinc-900 shadow overflow-hidden rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                        <thead class="bg-gray-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Time
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Event
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    User
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Details
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-900 divide-y divide-gray-200 dark:divide-zinc-700">
                            @foreach($logs as $log)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $log->created_at->format('M d, Y H:i:s') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span @class([
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' => in_array('model', $log->tags ?? []),
                                                'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300' => in_array('system', $log->tags ?? []),
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300' => in_array('security', $log->tags ?? []),
                                                'bg-gray-100 text-gray-800 dark:bg-gray-900/50 dark:text-gray-300' => empty($log->tags),
                                            ])>
                                                {{ $log->event }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $log->user?->name ?? 'System' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        <div class="max-w-lg break-words">
                                            @if($log->auditable_type)
                                                <div class="font-medium">
                                                    {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                                                </div>
                                            @endif

                                            @if($log->old_values || $log->new_values)
                                                <details class="mt-1">
                                                    <summary class="cursor-pointer text-sm text-emerald-600 dark:text-emerald-400">
                                                        View changes
                                                    </summary>
                                                    <div class="mt-2 space-y-1">
                                                        @if($log->old_values)
                                                            <div class="text-red-600 dark:text-red-400">
                                                                - {{ json_encode($log->old_values) }}
                                                            </div>
                                                        @endif
                                                        @if($log->new_values)
                                                            <div class="text-emerald-600 dark:text-emerald-400">
                                                                + {{ json_encode($log->new_values) }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </details>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($logs->hasPages())
                    <div class="bg-white dark:bg-zinc-900 px-4 py-3 border-t border-gray-200 dark:border-zinc-700 sm:px-6">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Services\AuditLogService;
use Carbon\Carbon;

new class extends Component 
{
    use WithPagination;
    
    #[Computed]
    protected $paginationTheme = 'tailwind';
    
    public array $filter = [
        'event' => '',
        'user_id' => '',
        'type' => '',
        'tag' => '',
        'date_from' => '',
        'date_to' => '',
        'per_page' => 15,
    ];
    public string $selectedTab = 'all'; // all, user, system, security
    public array $statistics = [];
    protected $auditLogService;

    public function mount(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
        $this->statistics = $auditLogService->getEventStatistics();
        
        // Log initial mount state
        \Log::info('SystemLogs component mounted', [
            'stats' => $this->statistics,
            'service_exists' => $this->auditLogService instanceof AuditLogService
        ]);
    }

    public function getLogCount()
    {
        return \App\Models\ActivityLog::count();
    }

    // Make logs available as a computed property
    public function logs()
    {
        try {
            $filters = $this->filter;
            $service = $this->getAuditLogService();
            
            if ($this->selectedTab !== 'all') {
                $filters['tag'] = $this->selectedTab;
            }

            // Ensure we have a valid per_page value
            $filters['per_page'] = $filters['per_page'] ?? 15;
            
            return match ($this->selectedTab) {
                'security' => $service->getSecurityEvents($filters),
                'system' => $service->getSystemEvents($filters),
                default => $service->getActivityLogs($filters),
            };
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Error fetching logs: ' . $e->getMessage());
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
        }
    }

    protected function getAuditLogService(): AuditLogService 
    {
        if (!$this->auditLogService) {
            $this->auditLogService = app(AuditLogService::class);
        }
        return $this->auditLogService;
    }

    public function refreshLogs()
    {
        $this->resetPage();
        $this->statistics = $this->getAuditLogService()->getEventStatistics();
    }

    public function selectTab(string $tab)
    {
        $this->selectedTab = $tab;
        $this->refreshLogs();
    }

    public function resetFilters()
    {
        $this->filter = [
            'event' => '',
            'user_id' => '',
            'type' => '',
            'tag' => '',
            'date_from' => '',
            'date_to' => '',
            'per_page' => 15,
        ];
        $this->refreshLogs();
    }

    public function applyFilters()
    {
        $this->refreshLogs();
    }
};

?>

<div class="max-w-7xl mx-auto backdrop-blur-sm bg-white/80 dark:bg-neutral-800/80 shadow-xl rounded-3xl p-6 lg:p-8 mt-8 border border-gray-100/40 dark:border-neutral-700/50 transition-all duration-300 relative overflow-hidden space-y-8">
    <!-- Header with glass morphism effect -->
    <div class="backdrop-blur-sm bg-white/80 dark:bg-zinc-800/80 shadow-xl rounded-2xl p-6 border border-gray-100/40 dark:border-zinc-700/50 transition-all duration-300 relative overflow-hidden">
        <!-- Decorative elements -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-emerald-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-gradient-to-tr from-teal-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
        
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="relative">
                    <h2 class="text-2xl md:text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                        <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-500 to-teal-400">
                            System & Audit Logs
                        </span>
                    </h2>
                    <!-- Animated decorative element -->
                    <div class="absolute -bottom-2 left-0 h-1 w-16 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full animate-pulse"></div>
                </div>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Monitor and analyze user actions, system events, and security alerts
                </p>
            </div>
            
            <div class="flex flex-wrap md:flex-nowrap gap-3">
                <button
                    wire:click="refreshLogs"
                    class="relative overflow-hidden inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-medium bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white transition-all duration-300 shadow-sm hover:shadow group"
                >
                    <span class="relative z-10 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 animate-spin-slow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12a9 9 0 1 1-6.219-8.56"></path>
                        </svg>
                        Refresh Logs
                    </span>
                    <!-- Shimmer effect -->
                    <span class="absolute top-0 right-full w-12 h-full bg-white/30 transform rotate-12 translate-x-0 transition-transform duration-1000 ease-out group-hover:translate-x-[400%]"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards with improved design -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div @class([
            'group relative overflow-hidden bg-white/60 dark:bg-zinc-800/40 rounded-xl border backdrop-blur-sm transition-all duration-300 hover:shadow-md hover:scale-[1.02] transform',
            'border-gray-200/60 dark:border-zinc-700/40' => $selectedTab !== 'all',
            'border-l-4 border-emerald-500 dark:border-emerald-400' => $selectedTab === 'all',
        ])>
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-100/20 to-transparent dark:from-emerald-900/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <button 
                type="button"
                wire:click="selectTab('all')"
                class="w-full p-5"
            >
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-emerald-100 dark:bg-emerald-900/20 rounded-full p-3 border border-emerald-200 dark:border-emerald-800/30 group-hover:scale-110 transform transition-all duration-300">
                        <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Total Events
                            </dt>
                            <dd class="flex items-baseline">
                                <span class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($statistics['total_events']) }}
                                </span>
                            </dd>
                        </dl>
                    </div>
                </div>
                <div class="w-full bg-gradient-to-r from-emerald-500 to-teal-500 h-0.5 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300 absolute bottom-0 left-0"></div>
            </button>
        </div>

        <div @class([
            'group relative overflow-hidden bg-white/60 dark:bg-zinc-800/40 rounded-xl border backdrop-blur-sm transition-all duration-300 hover:shadow-md hover:scale-[1.02] transform',
            'border-gray-200/60 dark:border-zinc-700/40' => $selectedTab !== 'user',
            'border-l-4 border-blue-500 dark:border-blue-400' => $selectedTab === 'user',
        ])>
            <div class="absolute inset-0 bg-gradient-to-br from-blue-100/20 to-transparent dark:from-blue-900/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <button 
                type="button"
                wire:click="selectTab('user')"
                class="w-full p-5"
            >
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 dark:bg-blue-900/20 rounded-full p-3 border border-blue-200 dark:border-blue-800/30 group-hover:scale-110 transform transition-all duration-300">
                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                User Actions
                            </dt>
                            <dd class="flex items-baseline">
                                <span class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($statistics['user_actions']) }}
                                </span>
                            </dd>
                        </dl>
                    </div>
                </div>
                <div class="w-full bg-gradient-to-r from-blue-500 to-indigo-500 h-0.5 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300 absolute bottom-0 left-0"></div>
            </button>
        </div>

        <div @class([
            'group relative overflow-hidden bg-white/60 dark:bg-zinc-800/40 rounded-xl border backdrop-blur-sm transition-all duration-300 hover:shadow-md hover:scale-[1.02] transform',
            'border-gray-200/60 dark:border-zinc-700/40' => $selectedTab !== 'system',
            'border-l-4 border-purple-500 dark:border-purple-400' => $selectedTab === 'system',
        ])>
            <div class="absolute inset-0 bg-gradient-to-br from-purple-100/20 to-transparent dark:from-purple-900/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <button 
                type="button"
                wire:click="selectTab('system')"
                class="w-full p-5"
            >
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 dark:bg-purple-900/20 rounded-full p-3 border border-purple-200 dark:border-purple-800/30 group-hover:scale-110 transform transition-all duration-300">
                        <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 01-3-3m3 3a3 3 0 100 6h13.5a3 3 0 100-6m-16.5-3a3 3 0 013-3h13.5a3 3 0 013 3m-19.5 0a4.5 4.5 0 01.9-2.7L5.737 5.1a3.375 3.375 0 012.7-1.35h7.126c1.062 0 2.062.5 2.7 1.35l2.587 3.45a4.5 4.5 0 01.9 2.7m0 0a3 3 0 01-3 3m0 3h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008zm-3 6h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                System Events
                            </dt>
                            <dd class="flex items-baseline">
                                <span class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($statistics['system_events']) }}
                                </span>
                            </dd>
                        </dl>
                    </div>
                </div>
                <div class="w-full bg-gradient-to-r from-purple-500 to-fuchsia-500 h-0.5 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300 absolute bottom-0 left-0"></div>
            </button>
        </div>

        <div @class([
            'group relative overflow-hidden bg-white/60 dark:bg-zinc-800/40 rounded-xl border backdrop-blur-sm transition-all duration-300 hover:shadow-md hover:scale-[1.02] transform',
            'border-gray-200/60 dark:border-zinc-700/40' => $selectedTab !== 'security',
            'border-l-4 border-amber-500 dark:border-amber-400' => $selectedTab === 'security',
        ])>
            <div class="absolute inset-0 bg-gradient-to-br from-amber-100/20 to-transparent dark:from-amber-900/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <button 
                type="button"
                wire:click="selectTab('security')"
                class="w-full p-5"
            >
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-amber-100 dark:bg-amber-900/20 rounded-full p-3 border border-amber-200 dark:border-amber-800/30 group-hover:scale-110 transform transition-all duration-300">
                        <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Security Events
                            </dt>
                            <dd class="flex items-baseline">
                                <span class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($statistics['security_events']) }}
                                </span>
                            </dd>
                        </dl>
                    </div>
                </div>
                <div class="w-full bg-gradient-to-r from-amber-500 to-orange-500 h-0.5 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300 absolute bottom-0 left-0"></div>
            </button>
        </div>
    </div>

    <!-- Filters with glass morphism effect -->
    <div class="backdrop-blur-sm bg-white/80 dark:bg-zinc-800/80 shadow-xl rounded-2xl border border-gray-100/40 dark:border-zinc-700/50 transition-all duration-300 p-6 relative overflow-hidden">
        <!-- Decorative elements -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-blue-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-gradient-to-tr from-purple-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
        
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                </svg>
                Filter Logs
            </h3>
            <span class="text-sm text-gray-500 dark:text-gray-400">
                {{ $this->logs()->total() }} results found
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-emerald-500 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input
                    type="search"
                    wire:model.live.debounce.300ms="filter.event"
                    placeholder="Search events..."
                    class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 dark:border-zinc-600 rounded-xl leading-5 bg-white/80 dark:bg-zinc-700/80 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition duration-200"
                />
                <div class="absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r from-emerald-500 to-teal-500 transform scale-x-0 group-focus-within:scale-x-100 transition-transform duration-300 origin-left rounded-full"></div>
            </div>
            
            <div class="relative group">
                <select
                    wire:model.live="filter.per_page"
                    class="appearance-none block w-full py-2.5 px-3 border border-gray-300 dark:border-zinc-600 bg-white/80 dark:bg-zinc-700/80 text-gray-900 dark:text-white rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition duration-200"
                >
                    <option value="15">15 per page</option>
                    <option value="30">30 per page</option>
                    <option value="50">50 per page</option>
                    <option value="100">100 per page</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 group-focus-within:text-emerald-500 transition-colors duration-200" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r from-emerald-500 to-teal-500 transform scale-x-0 group-focus-within:scale-x-100 transition-transform duration-300 origin-left rounded-full"></div>
            </div>
            
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 group-focus-within:text-emerald-500 transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <input
                    type="date"
                    wire:model.live.debounce.300ms="filter.date_from"
                    placeholder="From date"
                    class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 dark:border-zinc-600 rounded-xl leading-5 bg-white/80 dark:bg-zinc-700/80 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition duration-200"
                />
                <div class="absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r from-emerald-500 to-teal-500 transform scale-x-0 group-focus-within:scale-x-100 transition-transform duration-300 origin-left rounded-full"></div>
            </div>
            
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 group-focus-within:text-emerald-500 transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <input
                    type="date"
                    wire:model.live.debounce.300ms="filter.date_to"
                    placeholder="To date"
                    class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 dark:border-zinc-600 rounded-xl leading-5 bg-white/80 dark:bg-zinc-700/80 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition duration-200"
                />
                <div class="absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r from-emerald-500 to-teal-500 transform scale-x-0 group-focus-within:scale-x-100 transition-transform duration-300 origin-left rounded-full"></div>
            </div>
        </div>

        <div class="mt-4 flex justify-end space-x-3">
            <button
                wire:click="resetFilters"
                type="button"
                class="inline-flex items-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-xl shadow-sm bg-white dark:bg-zinc-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors duration-200"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Reset Filters
            </button>
        </div>
    </div>

    <!-- Debug Info -->
    <div class="mb-4 p-4 bg-gray-100 dark:bg-zinc-900 rounded-lg text-sm">
        <div class="font-mono space-y-1">
            <div>Total Records: {{ $this->getLogCount() }}</div>
            <div>Selected Tab: {{ $selectedTab }}</div>
            <div>Current Filter: {{ json_encode($filter) }}</div>
        </div>
    </div>

    <!-- Logs Table with glass morphism effect -->
    <div class="backdrop-blur-sm bg-white/80 dark:bg-zinc-800/80 shadow-xl rounded-2xl border border-gray-100/40 dark:border-zinc-700/50 transition-all duration-300 relative overflow-hidden">
        <!-- Decorative elements -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-gray-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-gradient-to-tr from-gray-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
        
        <div class="overflow-x-auto scrollbar-thin scrollbar-thumb-gray-200 dark:scrollbar-thumb-zinc-700 scrollbar-track-transparent">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                <thead class="bg-gray-50/90 dark:bg-zinc-800/50">
                    <tr>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Time</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Event</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Details</th>
                    </tr>
                </thead>
                <tbody class="bg-white/60 dark:bg-zinc-800/60 backdrop-blur-sm divide-y divide-gray-200 dark:divide-zinc-700">
                    @foreach($this->logs() as $log)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-zinc-700/30 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $log->created_at->format('M d, Y H:i:s') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span @class([
                                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                        'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' => in_array('model', $log->tags ?? []),
                                        'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300' => in_array('system', $log->tags ?? []),
                                        'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300' => in_array('security', $log->tags ?? []),
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
                                        <details class="mt-2">
                                            <summary class="cursor-pointer text-sm text-emerald-600 dark:text-emerald-400">
                                                View changes
                                            </summary>
                                            <div class="mt-2 space-y-2">
                                                @if($log->old_values)
                                                    <div class="text-red-600 dark:text-red-400">
                                                        - {{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}
                                                    </div>
                                                @endif
                                                @if($log->new_values)
                                                    <div class="text-emerald-600 dark:text-emerald-400">
                                                        + {{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}
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

        @if($this->logs()->hasPages())
            <div class="px-6 py-4 bg-gray-50/80 dark:bg-zinc-800/50 border-t border-gray-200/60 dark:border-zinc-700/50 rounded-b-2xl">
                {{ $this->logs()->links() }}
            </div>
        @endif
    </div>
</div>

<?php

use Livewire\Volt\Component;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;

new class extends Component {
    // State properties
    public string $search = '';
    public string $status = '';
    public int $perPage = 10;
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    
    // Convert these to computed properties to ensure they're accessible in the view
    #[Computed]
    public function statusOptions(): array
    {
        return [
            '' => 'All Statuses',
            'active' => 'Active',
            'trialing' => 'Trialing',
            'cancelled' => 'Cancelled',
            'expired' => 'Expired'
        ];
    }
    
    #[Computed]
    public function perPageOptions(): array
    {
        return [
            10 => '10 per page',
            25 => '25 per page',
            50 => '50 per page',
            100 => '100 per page'
        ];
    }
    
    #[Computed]
    public function subscriptions(): LengthAwarePaginator 
    {
        return Subscription::query()
            ->with(['user', 'plan', 'planVersion'])
            ->when($this->search, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                })->orWhereHas('plan', function ($q) {
                    $q->where('name', 'like', "%{$this->search}%");
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }
    
    #[Computed]
    public function stats(): array
    {
        $stats = [
            'total' => Subscription::count(),
            'active' => Subscription::where('status', 'active')->count(),
            'trial' => Subscription::whereNotNull('trial_ends_at')
                ->where('trial_ends_at', '>', now())
                ->count(),
            'cancelled' => Subscription::whereNotNull('cancelled_at')->count(),
        ];
        
        // Debug: Log the stats to see what's being returned
        \Log::info('Stats data:', $stats);
        
        // Ensure all values are integers
        foreach ($stats as $key => $value) {
            if (!is_numeric($value)) {
                \Log::error("Non-numeric value found in stats: {$key} = " . var_export($value, true));
                $stats[$key] = 0;
            }
        }
        
        return $stats;
    }
    
    public function sort(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }
}

?>

<div>
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                Subscription Management
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Manage and monitor user subscriptions
            </p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a
                href="{{ route('admin.subscriptions.customers') }}"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 mr-3"
            >
                Customer Overview
            </a>
            <a
                href="{{ route('admin.subscriptions.metrics') }}"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
            >
                Subscription Metrics
            </a>
        </div>
    </div>

    {{-- Stats Overview --}}
    <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <x-stats-card
            title="Total Subscriptions"
            :value="(int) ($this->stats['total'] ?? 0)"
            icon="document-text"
            trend="none"
        />
        
        <x-stats-card
            title="Active Subscriptions"
            :value="(int) ($this->stats['active'] ?? 0)"
            icon="check-circle"
            type="success"
            trend="none"
        />
        
        <x-stats-card
            title="Trial Subscriptions"
            :value="(int) ($this->stats['trial'] ?? 0)"
            icon="clock"
            type="info"
            trend="none"
        />
        
        <x-stats-card
            title="Cancelled"
            :value="(int) ($this->stats['cancelled'] ?? 0)"
            icon="x-circle"
            type="danger"
            trend="none"
        />
    </div>

    {{-- Filters --}}
    <div class="mt-6 bg-white dark:bg-zinc-800 shadow-sm rounded-lg divide-y dark:divide-zinc-700">
        <div class="px-4 py-5 sm:p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input
                        type="search"
                        wire:model.live="search"
                        placeholder="Search subscriptions..."
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-md leading-5 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
                    />
                </div>

                <select
                    wire:model.live="status"
                    class="block w-full py-2 px-3 border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
                >
                    @foreach($this->statusOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>

                <select
                    wire:model.live="perPage"
                    class="block w-full py-2 px-3 border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
                >
                    @foreach($this->perPageOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Subscriptions Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                <thead class="bg-gray-50 dark:bg-zinc-800/50">
                    <tr>
                        <th wire:click="sort('id')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer">
                            ID
                            @if($sortField === 'id')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            User
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Plan
                        </th>
                        <th wire:click="sort('status')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer">
                            Status
                            @if($sortField === 'status')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th wire:click="sort('current_period_ends_at')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer">
                            Next Payment
                            @if($sortField === 'current_period_ends_at')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-800 divide-y divide-gray-200 dark:divide-zinc-700">
                    @foreach($this->subscriptions as $subscription)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $subscription->id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $subscription->user->name }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $subscription->user->email }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ $subscription->plan->name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $subscription->planVersion->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-subscription-status :status="$subscription->status" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $subscription->current_period_ends_at?->format('M d, Y') ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.subscriptions.show', $subscription) }}" class="text-emerald-600 hover:text-emerald-900 dark:text-emerald-400 dark:hover:text-emerald-300">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-4 py-3 bg-gray-50 dark:bg-zinc-800/50">
            {{ $this->subscriptions->links() }}
        </div>
    </div>
</div>
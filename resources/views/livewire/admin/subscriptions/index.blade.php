<?php

use function Livewire\Volt\{state, computed, mount};
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

state([
    'search' => '',
    'status' => '',
    'perPage' => 10,
    'sortField' => 'created_at',
    'sortDirection' => 'desc'
]);

$subscriptions = computed(function (): LengthAwarePaginator {
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
});

$stats = computed(function () {
    return [
        'total' => Subscription::count(),
        'active' => Subscription::where('status', 'active')->count(),
        'trial' => Subscription::whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>', now())
            ->count(),
        'cancelled' => Subscription::whereNotNull('cancelled_at')->count(),
    ];
});

$sort = function (string $field) {
    if ($this->sortField === $field) {
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        $this->sortField = $field;
        $this->sortDirection = 'asc';
    }
};

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
    </div>

    {{-- Stats Overview --}}
    <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <flux:stat-card
            title="Total Subscriptions"
            :value="$this->stats['total']"
            icon="document-text"
            trend="none"
        />
        
        <flux:stat-card
            title="Active Subscriptions"
            :value="$this->stats['active']"
            icon="check-circle"
            type="success"
            trend="none"
        />
        
        <flux:stat-card
            title="Trial Subscriptions"
            :value="$this->stats['trial']"
            icon="clock"
            type="info"
            trend="none"
        />
        
        <flux:stat-card
            title="Cancelled"
            :value="$this->stats['cancelled']"
            icon="x-circle"
            type="danger"
            trend="none"
        />
    </div>

    {{-- Filters --}}
    <div class="mt-6 bg-white dark:bg-zinc-800 shadow-sm rounded-lg divide-y dark:divide-zinc-700">
        <div class="px-4 py-5 sm:p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <flux:input
                    type="search"
                    wire:model.live="search"
                    placeholder="Search subscriptions..."
                    icon="magnifying-glass"
                />

                <flux:select
                    wire:model.live="status"
                    :options="[
                        '' => 'All Statuses',
                        'active' => 'Active',
                        'trialing' => 'Trialing',
                        'cancelled' => 'Cancelled',
                        'expired' => 'Expired'
                    ]"
                />

                <flux:select
                    wire:model.live="perPage"
                    :options="[
                        10 => '10 per page',
                        25 => '25 per page',
                        50 => '50 per page',
                        100 => '100 per page'
                    ]"
                />
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

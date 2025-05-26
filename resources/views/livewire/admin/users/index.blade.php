<?php

use function Livewire\Volt\{state, computed, mount};
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

state([
    'search' => '',
    'role' => '',
    'status' => '',
    'perPage' => 10,
    'sortField' => 'created_at',
    'sortDirection' => 'desc',
    'selectedUsers' => [],
    'roles' => [],
    'selectedRole' => null,
    'showBulkRoleModal' => false
]);

mount(function () {
    $this->roles = Role::all();
});

$users = computed(function (): LengthAwarePaginator {
    return User::query()
        ->with(['roles', 'subscription.plan'])
        ->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            });
        })
        ->when($this->role, function ($query) {
            $query->role($this->role);
        })
        ->when($this->status, function ($query) {
            if ($this->status === 'subscribed') {
                $query->whereHas('subscription', function ($q) {
                    $q->where('status', 'active');
                });
            } elseif ($this->status === 'trial') {
                $query->whereHas('subscription', function ($q) {
                    $q->whereNotNull('trial_ends_at')
                        ->where('trial_ends_at', '>', now());
                });
            } elseif ($this->status === 'expired') {
                $query->whereHas('subscription', function ($q) {
                    $q->where('status', '!=', 'active');
                });
            } elseif ($this->status === 'none') {
                $query->doesntHave('subscription');
            }
        })
        ->orderBy($this->sortField, $this->sortDirection)
        ->paginate($this->perPage);
});

$stats = computed(function () {
    return [
        'total' => User::count(),
        'subscribed' => User::whereHas('subscription', function ($query) {
            $query->where('status', 'active');
        })->count(),
        'trial' => User::whereHas('subscription', function ($query) {
            $query->whereNotNull('trial_ends_at')
                ->where('trial_ends_at', '>', now());
        })->count(),
        'free' => User::doesntHave('subscription')->count(),
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

$toggleSelectAll = function () {
    if (count($this->selectedUsers) === $this->users->count()) {
        $this->selectedUsers = [];
    } else {
        $this->selectedUsers = $this->users->pluck('id')->map(fn($id) => (string) $id)->toArray();
    }
};

$updateUserRole = function () {
    if (!$this->selectedRole || empty($this->selectedUsers)) {
        return;
    }

    $role = Role::findById($this->selectedRole);
    User::whereIn('id', $this->selectedUsers)->each(function ($user) use ($role) {
        $user->syncRoles($role);
    });

    $this->selectedUsers = [];
    $this->selectedRole = null;
    $this->showBulkRoleModal = false;

    $this->dispatch('swal:toast', [
        'type' => 'success',
        'message' => 'User roles updated successfully.',
    ]);
};

?>

<div>
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                User Management
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Manage users and their roles
            </p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 space-x-2">
            <button
                wire:click="$set('showBulkRoleModal', true)"
                @class([
                    'inline-flex items-center px-4 py-2 text-sm font-medium rounded-md shadow-sm',
                    'text-white bg-emerald-600 hover:bg-emerald-700' => !empty($selectedUsers),
                    'text-gray-400 bg-gray-100 cursor-not-allowed' => empty($selectedUsers),
                ])
                @disabled(empty($selectedUsers))
            >
                Update Role
            </button>
        </div>
    </div>

    {{-- Stats Overview --}}
    <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <flux:stat-card
            title="Total Users"
            :value="$this->stats['total']"
            icon="users"
            trend="none"
        />
        
        <flux:stat-card
            title="Active Subscriptions"
            :value="$this->stats['subscribed']"
            icon="credit-card"
            type="success"
            trend="none"
        />
        
        <flux:stat-card
            title="Trial Users"
            :value="$this->stats['trial']"
            icon="clock"
            type="info"
            trend="none"
        />
        
        <flux:stat-card
            title="Free Users"
            :value="$this->stats['free']"
            icon="user"
            type="warning"
            trend="none"
        />
    </div>

    {{-- Filters --}}
    <div class="mt-6 bg-white dark:bg-zinc-800 shadow-sm rounded-lg divide-y dark:divide-zinc-700">
        <div class="px-4 py-5 sm:p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <flux:input
                    type="search"
                    wire:model.live="search"
                    placeholder="Search users..."
                    icon="magnifying-glass"
                />

                <flux:select
                    wire:model.live="role"
                    :options="['' => 'All Roles'] + $roles->pluck('name', 'name')->toArray()"
                />

                <flux:select
                    wire:model.live="status"
                    :options="[
                        '' => 'All Statuses',
                        'subscribed' => 'Active Subscription',
                        'trial' => 'Trial',
                        'expired' => 'Expired',
                        'none' => 'No Subscription'
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

        {{-- Users Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                <thead class="bg-gray-50 dark:bg-zinc-800/50">
                    <tr>
                        <th scope="col" class="relative w-12 px-6 sm:w-16 sm:px-8">
                            <input
                                type="checkbox"
                                class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                wire:click="toggleSelectAll"
                                @checked(count($selectedUsers) === $users->count())
                            >
                        </th>
                        <th wire:click="sort('name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer">
                            Name
                            @if($sortField === 'name')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th wire:click="sort('email')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer">
                            Email
                            @if($sortField === 'email')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Roles
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Subscription
                        </th>
                        <th wire:click="sort('created_at')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer">
                            Joined
                            @if($sortField === 'created_at')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-800 divide-y divide-gray-200 dark:divide-zinc-700">
                    @foreach($this->users as $user)
                        <tr>
                            <td class="relative w-12 px-6 sm:w-16 sm:px-8">
                                <input
                                    type="checkbox"
                                    value="{{ $user->id }}"
                                    wire:model.live="selectedUsers"
                                    class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                >
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0">
                                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/50">
                                            <span class="font-medium text-emerald-600 dark:text-emerald-300">
                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                            </span>
                                        </span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $user->name }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                @foreach($user->roles as $role)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->subscription)
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ $user->subscription->plan->name }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        <x-subscription-status :status="$user->subscription->status" />
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/50 dark:text-gray-300">
                                        Free
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.users.show', $user) }}" class="text-emerald-600 hover:text-emerald-900 dark:text-emerald-400 dark:hover:text-emerald-300">
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
            {{ $this->users->links() }}
        </div>
    </div>

    {{-- Bulk Role Update Modal --}}
    <x-dialog-modal wire:model.live="showBulkRoleModal">
        <x-slot name="title">
            Update Role for Selected Users
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Selected users: {{ count($selectedUsers) }}
                </p>

                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Select Role
                    </label>
                    <select
                        id="role"
                        wire:model.live="selectedRole"
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm rounded-md dark:bg-zinc-800 dark:border-zinc-700"
                    >
                        <option value="">Select a role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showBulkRoleModal', false)" wire:loading.attr="disabled">
                Cancel
            </x-secondary-button>

            <x-button class="ml-3" wire:click="updateUserRole" wire:loading.attr="disabled">
                Update Role
            </x-button>
        </x-slot>
    </x-dialog-modal>
</div>

<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

new class extends Component {
    use WithPagination;
    
    // Properties
    public $search = '';
    public $role = '';
    public $status = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $selectedUsers = [];
    public $roles = [];
    public $selectedRole = null;
    public $showBulkRoleModal = false;
    
    public function mount(): void
    {
        $this->roles = Role::all();
    }
    
    #[Computed]
    public function users(): LengthAwarePaginator
    {
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
    }
    
    #[Computed]
    public function stats(): array
    {
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
    
    public function toggleSelectAll(): void
    {
        if (count($this->selectedUsers) === $this->users->count()) {
            $this->selectedUsers = [];
        } else {
            $this->selectedUsers = $this->users->pluck('id')->map(fn($id) => (string) $id)->toArray();
        }
    }
    
    public function updateUserRole(): void
    {
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
                            User Management
                        </span>
                    </h2>
                    <!-- Animated decorative element -->
                    <div class="absolute -bottom-2 left-0 h-1 w-16 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full animate-pulse"></div>
                </div>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Monitor and manage user accounts, roles, and permissions
                </p>
            </div>
            
            <button
                wire:click="$set('showBulkRoleModal', true)"
                @class([
                    'relative overflow-hidden inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-300 shadow-sm hover:shadow group',
                    'bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white' => !empty($selectedUsers),
                    'bg-gray-100 dark:bg-zinc-700/50 text-gray-400 dark:text-gray-500 cursor-not-allowed' => empty($selectedUsers),
                ])
                @disabled(empty($selectedUsers))
            >
                <span class="relative z-10 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                    Update Role
                </span>
                @if(!empty($selectedUsers))
                    <!-- Shimmer effect -->
                    <span class="absolute top-0 right-full w-12 h-full bg-white/30 transform rotate-12 translate-x-0 transition-transform duration-1000 ease-out group-hover:translate-x-[400%]"></span>
                @endif
            </button>
        </div>
    </div>

    <!-- Stats Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Total Users -->
        <div class="group bg-white/60 dark:bg-zinc-800/40 rounded-xl overflow-hidden border border-gray-200/60 dark:border-zinc-700/40 backdrop-blur-sm shadow-sm transition-all duration-300 hover:shadow-md">
            <div class="px-5 py-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-emerald-50 dark:bg-emerald-900/20 rounded-full p-3 border border-emerald-100 dark:border-emerald-800/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                        </svg>
                    </div>
                    
                    <div class="ml-4 flex-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                            Total Users
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                            {{ number_format($this->stats['total']) }}
                        </dd>
                    </div>
                </div>
            </div>
            <div class="w-full bg-gradient-to-r from-emerald-500 to-teal-500 h-1 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
        </div>
        
        <!-- Active Subscriptions -->
        <div class="group bg-white/60 dark:bg-zinc-800/40 rounded-xl overflow-hidden border border-gray-200/60 dark:border-zinc-700/40 backdrop-blur-sm shadow-sm transition-all duration-300 hover:shadow-md">
            <div class="px-5 py-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-50 dark:bg-green-900/20 rounded-full p-3 border border-green-100 dark:border-green-800/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                            <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    
                    <div class="ml-4 flex-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                            Active Subscriptions
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                            {{ number_format($this->stats['subscribed']) }}
                        </dd>
                    </div>
                </div>
            </div>
            <div class="w-full bg-gradient-to-r from-green-500 to-emerald-500 h-1 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
        </div>
        
        <!-- Trial Users -->
        <div class="group bg-white/60 dark:bg-zinc-800/40 rounded-xl overflow-hidden border border-gray-200/60 dark:border-zinc-700/40 backdrop-blur-sm shadow-sm transition-all duration-300 hover:shadow-md">
            <div class="px-5 py-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-50 dark:bg-blue-900/20 rounded-full p-3 border border-blue-100 dark:border-blue-800/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    
                    <div class="ml-4 flex-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                            Trial Users
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                            {{ number_format($this->stats['trial']) }}
                        </dd>
                    </div>
                </div>
            </div>
            <div class="w-full bg-gradient-to-r from-blue-500 to-sky-500 h-1 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
        </div>
        
        <!-- Free Users -->
        <div class="group bg-white/60 dark:bg-zinc-800/40 rounded-xl overflow-hidden border border-gray-200/60 dark:border-zinc-700/40 backdrop-blur-sm shadow-sm transition-all duration-300 hover:shadow-md">
            <div class="px-5 py-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-amber-50 dark:bg-amber-900/20 rounded-full p-3 border border-amber-100 dark:border-amber-800/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    
                    <div class="ml-4 flex-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                            Free Users
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                            {{ number_format($this->stats['free']) }}
                        </dd>
                    </div>
                </div>
            </div>
            <div class="w-full bg-gradient-to-r from-amber-500 to-yellow-500 h-1 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
        </div>
    </div>

    <!-- Filters and Table Section -->
    <div class="backdrop-blur-sm bg-white/80 dark:bg-zinc-800/80 shadow-xl rounded-2xl border border-gray-100/40 dark:border-zinc-700/50 transition-all duration-300 relative overflow-hidden">
        <!-- Decorative elements -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-teal-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-gradient-to-tr from-emerald-400/10 to-transparent rounded-full blur-3xl -z-10"></div>
        
        <!-- Filters -->
        <div class="p-5 lg:p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Search -->
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-emerald-500 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input
                        type="search"
                        wire:model.live="search"
                        placeholder="Search users..."
                        class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 dark:border-zinc-600 rounded-xl leading-5 bg-white/80 dark:bg-zinc-700/80 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition duration-200"
                    />
                    <div class="absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r from-emerald-500 to-teal-500 transform scale-x-0 group-focus-within:scale-x-100 transition-transform duration-300 origin-left rounded-full"></div>
                </div>

                <!-- Role filter -->
                <div class="relative group">
                    <select
                        wire:model.live="role"
                        class="appearance-none block w-full py-2.5 px-3 border border-gray-300 dark:border-zinc-600 bg-white/80 dark:bg-zinc-700/80 text-gray-900 dark:text-white rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition duration-200"
                    >
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 group-focus-within:text-emerald-500 transition-colors duration-200" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r from-emerald-500 to-teal-500 transform scale-x-0 group-focus-within:scale-x-100 transition-transform duration-300 origin-left rounded-full"></div>
                </div>

                <!-- Status filter -->
                <div class="relative group">
                    <select
                        wire:model.live="status"
                        class="appearance-none block w-full py-2.5 px-3 border border-gray-300 dark:border-zinc-600 bg-white/80 dark:bg-zinc-700/80 text-gray-900 dark:text-white rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition duration-200"
                    >
                        <option value="">All Statuses</option>
                        <option value="subscribed">Active Subscription</option>
                        <option value="trial">Trial</option>
                        <option value="expired">Expired</option>
                        <option value="none">No Subscription</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 group-focus-within:text-emerald-500 transition-colors duration-200" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r from-emerald-500 to-teal-500 transform scale-x-0 group-focus-within:scale-x-100 transition-transform duration-300 origin-left rounded-full"></div>
                </div>

                <!-- Per page options -->
                <div class="relative group">
                    <select
                        wire:model.live="perPage"
                        class="appearance-none block w-full py-2.5 px-3 border border-gray-300 dark:border-zinc-600 bg-white/80 dark:bg-zinc-700/80 text-gray-900 dark:text-white rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition duration-200"
                    >
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
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
            </div>
        </div>

        <!-- Users Table -->
        <div class="overflow-x-auto scrollbar-thin scrollbar-thumb-gray-200 dark:scrollbar-thumb-zinc-700 scrollbar-track-transparent">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                <thead>
                    <tr class="bg-gray-50/90 dark:bg-zinc-800/50">
                        <th scope="col" class="relative w-12 px-6 sm:w-16 sm:px-8">
                            <input
                                type="checkbox"
                                class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                wire:click="toggleSelectAll"
                                @checked(count($selectedUsers) === $this->users->count())
                            >
                        </th>
                        <th wire:click="sort('name')" class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors duration-200 select-none">
                            <div class="flex items-center">
                                Name
                                @if($sortField === 'name')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 h-4 w-4 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                                        @if($sortDirection === 'asc')
                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                        @else
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        @endif
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th wire:click="sort('email')" class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors duration-200 select-none">
                            <div class="flex items-center">
                                Email
                                @if($sortField === 'email')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 h-4 w-4 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                                        @if($sortDirection === 'asc')
                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                        @else
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        @endif
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Roles
                        </th>
                        <th class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Subscription
                        </th>
                        <th wire:click="sort('created_at')" class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors duration-200 select-none">
                            <div class="flex items-center">
                                Joined
                                @if($sortField === 'created_at')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 h-4 w-4 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                                        @if($sortDirection === 'asc')
                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                        @else
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        @endif
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3.5 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white/60 dark:bg-zinc-800/60 backdrop-blur-sm divide-y divide-gray-200 dark:divide-zinc-700">
                    @forelse($this->users as $user)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-zinc-700/30 transition-colors duration-150">
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
                                    <div class="flex-shrink-0 h-9 w-9 rounded-full bg-gradient-to-br from-emerald-500/80 to-teal-500/80 text-white flex items-center justify-center text-sm font-medium">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $user->name }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($user->roles as $role)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300 border border-emerald-200/50 dark:border-emerald-800/30">
                                            {{ $role->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->subscription)
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $user->subscription->plan->name }}
                                    </div>
                                    <div class="mt-1">
                                        @if($user->subscription->status === 'active')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800/20 dark:text-green-400 border border-green-200 dark:border-green-800/30">
                                                <svg class="mr-1.5 h-2 w-2 text-green-500" fill="currentColor" viewBox="0 0 8 8">
                                                    <circle cx="4" cy="4" r="3" />
                                                </svg>
                                                Active
                                            </span>
                                        @elseif($user->subscription->status === 'trialing')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800/20 dark:text-blue-400 border border-blue-200 dark:border-blue-800/30">
                                                <svg class="mr-1.5 h-2 w-2 text-blue-500" fill="currentColor" viewBox="0 0 8 8">
                                                    <circle cx="4" cy="4" r="3" />
                                                </svg>
                                                Trial
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-800/20 dark:text-red-400 border border-red-200 dark:border-red-800/30">
                                                <svg class="mr-1.5 h-2 w-2 text-red-500" fill="currentColor" viewBox="0 0 8 8">
                                                    <circle cx="4" cy="4" r="3" />
                                                </svg>
                                                {{ ucfirst($user->subscription->status) }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                                        <svg class="mr-1.5 h-2 w-2 text-gray-500" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3" />
                                        </svg>
                                        Free
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $user->created_at->format('M d, Y') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <a 
                                    href="{{ route('admin.users.show', $user) }}" 
                                    class="group inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:hover:bg-emerald-900/40 transition-colors duration-200"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 group-hover:animate-pulse" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                    </svg>
                                    View Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <p class="text-sm font-medium">No users found</p>
                                    <p class="text-xs mt-1">Try adjusting your search or filter criteria</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 bg-gray-50/80 dark:bg-zinc-800/50 border-t border-gray-200/60 dark:border-zinc-700/50 rounded-b-2xl">
            {{ $this->users->links() }}
        </div>
    </div>

    <!-- Bulk Role Update Modal -->
    <flux:modal wire:model.live="showBulkRoleModal">
        <x-slot name="title">
            <div class="flex items-center">
                <div class="mr-3 p-2 bg-emerald-100 dark:bg-emerald-900/30 rounded-full text-emerald-600 dark:text-emerald-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Update Role for Selected Users
                </h3>
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-lg p-4 border border-emerald-100 dark:border-emerald-900/30">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                        </svg>
                        <p class="text-sm font-medium text-emerald-700 dark:text-emerald-400">
                            {{ count($selectedUsers) }} {{ Str::plural('user', count($selectedUsers)) }} selected
                        </p>
                    </div>
                </div>

                <div class="relative group">
                    <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Select Role to Assign
                    </label>
                    <select
                        id="role"
                        wire:model.live="selectedRole"
                        class="appearance-none block w-full py-2.5 px-3 border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-700/80 text-gray-900 dark:text-white rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition duration-200"
                    >
                        <option value="">Select a role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 top-6 right-0 flex items-center pr-3 pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 group-focus-within:text-emerald-500 transition-colors duration-200" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r from-emerald-500 to-teal-500 transform scale-x-0 group-focus-within:scale-x-100 transition-transform duration-300 origin-left rounded-full"></div>
                </div>

                <div class="mt-2 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-100 dark:border-amber-900/30">
                    <div class="flex">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <p class="text-xs text-amber-700 dark:text-amber-400">
                            This action will replace all existing roles for the selected users with the new role.
                        </p>
                    </div>
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <div class="flex justify-end space-x-3">
                <button 
                    wire:click="$set('showBulkRoleModal', false)" 
                    type="button"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-700 transition-colors duration-200"
                >
                    Cancel
                </button>

                <button 
                    wire:click="updateUserRole"
                    type="button"
                    @class([
                        'relative overflow-hidden inline-flex items-center px-4 py-2 rounded-xl text-sm font-medium transition-all duration-300 shadow-sm group',
                        'bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white' => $selectedRole,
                        'bg-gray-100 dark:bg-zinc-700/50 text-gray-400 dark:text-gray-500 cursor-not-allowed' => !$selectedRole,
                    ])
                    @disabled(!$selectedRole)
                >
                    <span class="relative z-10 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Update Role
                    </span>
                    @if($selectedRole)
                        <!-- Shimmer effect -->
                        <span class="absolute top-0 right-full w-12 h-full bg-white/30 transform rotate-12 translate-x-0 transition-transform duration-1000 ease-out group-hover:translate-x-[400%]"></span>
                    @endif
                </button>
            </div>
        </x-slot>
    </flux:modal>
</div>

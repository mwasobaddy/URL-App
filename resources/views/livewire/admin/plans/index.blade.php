<?php

use Livewire\Attributes\Computed;
use Livewire\Volt\Component;
use App\Models\Plan;

new class extends Component {
    // Properties
    public string $search = '';
    public int $perPage = 10;
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public bool $showDeleteModal = false;
    public ?Plan $planToDelete = null;

    // Methods
    public function sort($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function confirmDelete(Plan $plan)
    {
        $this->planToDelete = $plan;
        $this->showDeleteModal = true;
    }

    public function deletePlan()
    {
        $this->planToDelete->delete();
        $this->planToDelete = null;
        $this->showDeleteModal = false;
        $this->dispatch('swal:toast', [
            'type' => 'success',
            'message' => 'Plan deleted successfully.',
        ]);
    }

    // Computed properties
    #[Computed]
    public function plans()
    {
        return Plan::query()
            ->when($this->search, fn($query) => $query->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    #[Computed]
    public function stats()
    {
        return [
            'total' => Plan::count(),
            'active' => Plan::where('is_active', true)->count(),
            'featured' => Plan::where('is_featured', true)->count(),
            'archived' => Plan::onlyTrashed()->count(),
        ];
    }
};

?>

<div>
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                Plan Management
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Manage subscription plans and their features
            </p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a 
                href="{{ route('admin.plans.create') }}"
                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
            >
                Create Plan
            </a>
        </div>
    </div>

    {{-- Stats Overview --}}
    <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <x-stats-card
            title="Total Plans"
            :value="$this->stats['total']"
            icon="document-text"
            trend="none"
        />
        
        <x-stats-card
            title="Active Plans"
            :value="$this->stats['active']"
            icon="check-circle"
            type="success"
            trend="none"
        />
        
        <x-stats-card
            title="Featured Plans"
            :value="$this->stats['featured']"
            icon="star"
            type="info"
            trend="none"
        />
        
        <x-stats-card
            title="Archived Plans"
            :value="$this->stats['archived']"
            icon="archive"
            type="warning"
            trend="none"
        />
    </div>

    {{-- Filters --}}
    <div class="mt-6 bg-white dark:bg-zinc-800 shadow-sm rounded-lg divide-y dark:divide-zinc-700">
        <div class="px-4 py-5 sm:p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-2">
                <flux:input
                    type="search"
                    wire:model.live="search"
                    placeholder="Search plans..."
                    icon="magnifying-glass"
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

        {{-- Plans Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                <thead>
                    <tr>
                        <th wire:click="sort('name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer">
                            Name
                            @if($sortField === 'name')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th wire:click="sort('monthly_price')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer">
                            Monthly Price
                            @if($sortField === 'monthly_price')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th wire:click="sort('yearly_price')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer">
                            Yearly Price
                            @if($sortField === 'yearly_price')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th wire:click="sort('is_active')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer">
                            Status
                            @if($sortField === 'is_active')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th wire:click="sort('is_featured')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer">
                            Featured
                            @if($sortField === 'is_featured')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Versions
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-800 divide-y divide-gray-200 dark:divide-zinc-700">
                    @foreach($this->plans as $plan)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ $plan->name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $plan->description }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">${{ number_format($plan->monthly_price, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">${{ number_format($plan->yearly_price, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($plan->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($plan->is_featured)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">
                                        Featured
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/50 dark:text-gray-300">
                                        Standard
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    {{ $plan->versions()->count() }} versions
                                </div>
                                @if($activeVersion = $plan->getCurrentVersion())
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        Current: v{{ $activeVersion->version }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.plans.edit', $plan) }}" class="text-emerald-600 hover:text-emerald-900 dark:text-emerald-400 dark:hover:text-emerald-300 mr-3">
                                    Edit
                                </a>
                                <button
                                    wire:click="confirmDelete({{ $plan->id }})"
                                    wire:confirm="Are you sure you want to delete this plan?"
                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-4 py-3 bg-gray-50 dark:bg-zinc-800/50">
            {{ $this->plans->links() }}
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <flux:modal wire:model.live="showDeleteModal">
        <x-slot name="title">
            Delete Plan
        </x-slot>

        <x-slot name="content">
            <div class="text-sm text-gray-600 dark:text-gray-400">
                Are you sure you want to delete this plan? This action cannot be undone.
            </div>
        </x-slot>

        <x-slot name="footer">
            <flux:button wire:click="$set('showDeleteModal', false)" wire:loading.attr="disabled">
                Cancel
            </flux:button>

            <x-danger-button class="ml-3" wire:click="deletePlan" wire:loading.attr="disabled">
                Delete Plan
            </x-danger-button>
        </x-slot>
    </flux:modal>
</div>

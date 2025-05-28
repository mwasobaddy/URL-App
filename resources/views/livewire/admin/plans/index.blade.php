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
                            Plan Management
                        </span>
                    </h2>
                    <!-- Animated decorative element -->
                    <div class="absolute -bottom-2 left-0 h-1 w-16 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full animate-pulse"></div>
                </div>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Manage subscription plans and their features
                </p>
            </div>
            
            <a 
                href="{{ route('admin.plans.create') }}"
                class="relative overflow-hidden inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-medium bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white transition-all duration-300 shadow-sm hover:shadow group"
            >
                <span class="relative z-10 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Create Plan
                </span>
                <!-- Shimmer effect -->
                <span class="absolute top-0 right-full w-12 h-full bg-white/30 transform rotate-12 translate-x-0 transition-transform duration-1000 ease-out group-hover:translate-x-[400%]"></span>
            </a>
        </div>
    </div>

    <!-- Stats Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Total Plans -->
        <div class="group bg-white/60 dark:bg-zinc-800/40 rounded-xl overflow-hidden border border-gray-200/60 dark:border-zinc-700/40 backdrop-blur-sm shadow-sm transition-all duration-300 hover:shadow-md">
            <div class="px-5 py-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-emerald-50 dark:bg-emerald-900/20 rounded-full p-3 border border-emerald-100 dark:border-emerald-800/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    
                    <div class="ml-4 flex-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                            Total Plans
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                            {{ number_format($this->stats['total']) }}
                        </dd>
                    </div>
                </div>
            </div>
            <div class="w-full bg-gradient-to-r from-emerald-500 to-teal-500 h-1 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
        </div>
        
        <!-- Active Plans -->
        <div class="group bg-white/60 dark:bg-zinc-800/40 rounded-xl overflow-hidden border border-gray-200/60 dark:border-zinc-700/40 backdrop-blur-sm shadow-sm transition-all duration-300 hover:shadow-md">
            <div class="px-5 py-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-50 dark:bg-green-900/20 rounded-full p-3 border border-green-100 dark:border-green-800/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    
                    <div class="ml-4 flex-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                            Active Plans
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                            {{ number_format($this->stats['active']) }}
                        </dd>
                    </div>
                </div>
            </div>
            <div class="w-full bg-gradient-to-r from-green-500 to-emerald-500 h-1 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
        </div>
        
        <!-- Featured Plans -->
        <div class="group bg-white/60 dark:bg-zinc-800/40 rounded-xl overflow-hidden border border-gray-200/60 dark:border-zinc-700/40 backdrop-blur-sm shadow-sm transition-all duration-300 hover:shadow-md">
            <div class="px-5 py-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-50 dark:bg-blue-900/20 rounded-full p-3 border border-blue-100 dark:border-blue-800/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </div>
                    
                    <div class="ml-4 flex-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                            Featured Plans
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                            {{ number_format($this->stats['featured']) }}
                        </dd>
                    </div>
                </div>
            </div>
            <div class="w-full bg-gradient-to-r from-blue-500 to-sky-500 h-1 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
        </div>
        
        <!-- Archived Plans -->
        <div class="group bg-white/60 dark:bg-zinc-800/40 rounded-xl overflow-hidden border border-gray-200/60 dark:border-zinc-700/40 backdrop-blur-sm shadow-sm transition-all duration-300 hover:shadow-md">
            <div class="px-5 py-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-amber-50 dark:bg-amber-900/20 rounded-full p-3 border border-amber-100 dark:border-amber-800/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-500" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z" />
                            <path fill-rule="evenodd" d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    
                    <div class="ml-4 flex-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                            Archived Plans
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                            {{ number_format($this->stats['archived']) }}
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
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
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
                        placeholder="Search plans..."
                        class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 dark:border-zinc-600 rounded-xl leading-5 bg-white/80 dark:bg-zinc-700/80 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition duration-200"
                    />
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

        <!-- Plans Table -->
        <div class="overflow-x-auto scrollbar-thin scrollbar-thumb-gray-200 dark:scrollbar-thumb-zinc-700 scrollbar-track-transparent">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                <thead>
                    <tr class="bg-gray-50/90 dark:bg-zinc-800/50">
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
                        <th wire:click="sort('monthly_price')" class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors duration-200 select-none">
                            <div class="flex items-center">
                                Monthly Price
                                @if($sortField === 'monthly_price')
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
                        <th wire:click="sort('yearly_price')" class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors duration-200 select-none">
                            <div class="flex items-center">
                                Yearly Price
                                @if($sortField === 'yearly_price')
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
                        <th wire:click="sort('is_active')" class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors duration-200 select-none">
                            <div class="flex items-center">
                                Status
                                @if($sortField === 'is_active')
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
                        <th wire:click="sort('is_featured')" class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors duration-200 select-none">
                            <div class="flex items-center">
                                Featured
                                @if($sortField === 'is_featured')
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
                            Versions
                        </th>
                        <th class="px-6 py-3.5 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white/60 dark:bg-zinc-800/60 backdrop-blur-sm divide-y divide-gray-200 dark:divide-zinc-700">
                    @forelse($this->plans as $plan)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-zinc-700/30 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-9 w-9 rounded-full bg-gradient-to-br from-emerald-500/80 to-teal-500/80 text-white flex items-center justify-center text-sm font-medium">
                                        {{ substr($plan->name, 0, 1) }}
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $plan->name }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ Str::limit($plan->description, 40) }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    ${{ number_format($plan->monthly_price, 2) }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    per month
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    ${{ number_format($plan->yearly_price, 2) }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    per year
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($plan->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800/20 dark:text-green-400 border border-green-200 dark:border-green-800/30">
                                        <svg class="mr-1.5 h-2 w-2 text-green-500" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3" />
                                        </svg>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-800/20 dark:text-red-400 border border-red-200 dark:border-red-800/30">
                                        <svg class="mr-1.5 h-2 w-2 text-red-500" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3" />
                                        </svg>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($plan->is_featured)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-800/20 dark:text-amber-400 border border-amber-200 dark:border-amber-800/30">
                                        <svg class="mr-1.5 h-2 w-2 text-amber-500" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3" />
                                        </svg>
                                        Featured
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800/20 dark:text-gray-400 border border-gray-200 dark:border-gray-800/30">
                                        <svg class="mr-1.5 h-2 w-2 text-gray-500" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3" />
                                        </svg>
                                        Standard
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($activeVersion = $plan->getCurrentVersion())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="mr-1.5 h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                        </svg>
                                        v{{ $activeVersion->version }}
                                    </span>
                                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        {{ $plan->versions()->count() }} total versions
                                    </div>
                                @else
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $plan->versions()->count() }} versions
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <a
                                        href="{{ route('admin.plans.edit', $plan) }}"
                                        class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:hover:bg-emerald-900/40 transition-colors duration-200"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                        </svg>
                                        Edit
                                    </a>
                                    <button
                                        wire:click="confirmDelete({{ $plan->id }})"
                                        wire:confirm="Are you sure you want to delete this plan?"
                                        class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded-lg bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40 transition-colors duration-200"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 dark:text-gray-600 mb-3" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5 5a3 3 0 015-2.236A3 3 0 0114.83 6H16a2 2 0 110 4h-5V9a1 1 0 10-2 0v1H4a2 2 0 110-4h1.17C5.06 5.687 5 5.35 5 5zm4 1V5a1 1 0 10-1 1h1zm3 0a1 1 0 10-1-1v1h1z" clip-rule="evenodd" />
                                        <path d="M9 11H3v5a2 2 0 002 2h4v-7zM11 18h4a2 2 0 002-2v-5h-6v7z" />
                                    </svg>
                                    <p class="text-sm font-medium">No plans found</p>
                                    <p class="text-xs mt-1">Try adjusting your search criteria or create a new plan</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 bg-gray-50/80 dark:bg-zinc-800/50 border-t border-gray-200/60 dark:border-zinc-700/50 rounded-b-2xl">
            {{ $this->plans->links() }}
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <flux:modal wire:model.live="showDeleteModal" maxWidth="md">
        <x-slot name="title">
            <div class="flex items-center">
                <div class="bg-red-100 dark:bg-red-900/30 p-2 rounded-full mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Delete Plan</h3>
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="mt-2">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Are you sure you want to delete this plan? This action cannot be undone and will remove all associated data.
                </p>
                
                @if($planToDelete)
                    <div class="mt-4 p-4 bg-gray-50 dark:bg-zinc-800/50 rounded-lg border border-gray-100 dark:border-zinc-700/50">
                        <div class="font-semibold text-gray-900 dark:text-white">{{ $planToDelete->name }}</div>
                        <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $planToDelete->description }}</div>
                    </div>
                @endif
            </div>
        </x-slot>

        <x-slot name="footer">
            <div class="flex justify-end space-x-3">
                <button
                    wire:click="$set('showDeleteModal', false)"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
                >
                    Cancel
                </button>
                
                <button
                    wire:click="deletePlan"
                    class="relative overflow-hidden inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 group"
                >
                    <span class="relative z-10 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        Delete Plan
                    </span>
                    <span class="absolute top-0 right-full w-12 h-full bg-white/20 transform rotate-12 translate-x-0 transition-transform duration-1000 ease-out group-hover:translate-x-[350%]"></span>
                </button>
            </div>
        </x-slot>
    </flux:modal>
</div>
